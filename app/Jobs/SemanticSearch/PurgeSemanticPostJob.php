<?php

namespace App\Jobs\SemanticSearch;

use App\Services\SemanticSearch\PostSemanticIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Xóa vector Qdrant + rows chunk theo post_id (sau khi post đã xóa khỏi DB vẫn chạy được).
 */
class PurgeSemanticPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(public int $postId)
    {
        $this->onQueue((string) config('semantic_search.queue.interactive', 'semantic'));
    }

    public function handle(PostSemanticIndexer $indexer): void
    {
        if (config('semantic_search.embedding.provider') !== 'local_http') {
            return;
        }

        try {
            $indexer->purgePost($this->postId);
        } catch (Throwable $e) {
            Log::warning('PurgeSemanticPostJob failed', [
                'post_id' => $this->postId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
