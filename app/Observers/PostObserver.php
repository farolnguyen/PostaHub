<?php

namespace App\Observers;

use App\Jobs\SemanticSearch\PurgeSemanticPostJob;
use App\Jobs\SemanticSearch\ReindexSemanticPostJob;
use App\Models\Post;
use App\Services\SemanticSearch\PostSemanticIndexer;
use Illuminate\Support\Facades\Log;
use Throwable;

class PostObserver
{
    public function saved(Post $post): void
    {
        if (! (bool) config('semantic_search.enabled', false)) {
            return;
        }

        if (config('semantic_search.embedding.provider') !== 'local_http') {
            return;
        }

        if ($post->wasRecentlyCreated) {
            ReindexSemanticPostJob::dispatch($post->id, 'interactive');
            Post::markSemanticIndexPending((int) $post->id);

            return;
        }

        if ($post->wasChanged(['title', 'content'])) {
            ReindexSemanticPostJob::dispatch($post->id, 'interactive');
            Post::markSemanticIndexPending((int) $post->id);
        }
    }

    /**
     * Luôn cố gắng dọn Qdrant/DB chunk khi post bị xóa (không phụ thuộc SEMANTIC_SEARCH_ENABLED)
     * để tránh vector orphan.
     */
    public function deleted(Post $post): void
    {
        if (config('semantic_search.embedding.provider') !== 'local_http') {
            return;
        }

        $id = (int) $post->getKey();
        if ($id <= 0) {
            return;
        }

        try {
            app(PostSemanticIndexer::class)->purgePost($id);
        } catch (Throwable $e) {
            Log::warning('Semantic purge on post delete failed', [
                'post_id' => $id,
                'message' => $e->getMessage(),
            ]);
            PurgeSemanticPostJob::dispatch($id);
        }
    }
}
