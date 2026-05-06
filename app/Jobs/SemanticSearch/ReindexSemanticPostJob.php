<?php

namespace App\Jobs\SemanticSearch;

use App\Models\Post;
use App\Services\SemanticSearch\PostSemanticIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReindexSemanticPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(
        public int $postId,
        public string $source = 'interactive',
    ) {
        $this->onQueue(
            $source === 'bulk'
                ? (string) config('semantic_search.queue.bulk', 'semantic-bulk')
                : (string) config('semantic_search.queue.interactive', 'semantic')
        );
    }

    public function handle(PostSemanticIndexer $indexer): void
    {
        if (! (bool) config('semantic_search.enabled', false)) {
            return;
        }

        if (config('semantic_search.embedding.provider') !== 'local_http') {
            return;
        }

        $post = Post::query()->find($this->postId);
        if ($post === null) {
            return;
        }

        try {
            $indexer->indexPost($post);
        } catch (Throwable $e) {
            Log::error('ReindexSemanticPostJob failed', [
                'post_id' => $this->postId,
                'source' => $this->source,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception === null) {
            return;
        }

        Post::markSemanticIndexFailed($this->postId, $exception->getMessage());
    }
}
