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

/**
 * Index theo lô post (URL prefix) + chain job tiếp sau delay — dùng sau seed/import khối lượng lớn.
 */
class SemanticBulkReindexBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 600;

    public function __construct(
        public int $cursorId = 0,
        public ?string $urlLikePattern = null,
    ) {
        $this->onQueue((string) config('semantic_search.queue.bulk', 'semantic-bulk'));
    }

    public function handle(PostSemanticIndexer $indexer): void
    {
        if (! (bool) config('semantic_search.enabled', false)) {
            return;
        }

        if (config('semantic_search.embedding.provider') !== 'local_http') {
            return;
        }

        $batch = (int) config('semantic_search.queue.bulk_reindex_batch_posts', 40);
        $batch = max(1, min(200, $batch));
        $delay = max(0, (int) config('semantic_search.queue.bulk_reindex_delay_seconds', 12));

        $q = Post::query()
            ->where('id', '>', $this->cursorId)
            ->orderBy('id');

        if ($this->urlLikePattern !== null && $this->urlLikePattern !== '') {
            $q->where('url', 'like', $this->urlLikePattern);
        }

        $posts = $q->limit($batch)->get();
        if ($posts->isEmpty()) {
            return;
        }

        foreach ($posts as $post) {
            try {
                $indexer->indexPost($post);
            } catch (Throwable $e) {
                Log::error('SemanticBulkReindexBatchJob row failed', [
                    'post_id' => $post->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $lastId = (int) $posts->last()->id;
        if ($posts->count() < $batch) {
            return;
        }

        self::dispatch($lastId, $this->urlLikePattern)
            ->onQueue((string) config('semantic_search.queue.bulk', 'semantic-bulk'))
            ->delay(now()->addSeconds($delay));
    }
}
