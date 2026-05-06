<?php

namespace App\Services\SemanticSearch;

use App\Models\Post;
use App\Models\PostSemanticChunk;
use App\Support\SemanticChunker;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Throwable;

class PostSemanticIndexer
{
    public function __construct(
        private readonly EmbeddingHttpClient $embedding,
        private readonly QdrantHttpClient $qdrant,
    ) {}

    /**
     * Deterministic Qdrant point id (UUID v5) per post + chunk_index.
     */
    public function stablePointId(int $postId, int $chunkIndex): string
    {
        return Uuid::uuid5(
            Uuid::NAMESPACE_URL,
            'https://postahub.local/semantic/post/'.$postId.'/chunk/'.$chunkIndex
        )->toString();
    }

    /**
     * Xóa toàn bộ vector + rows chunk của post (dùng khi reindex hoặc sau khi xóa post — phase 4).
     */
    public function purgePost(int $postId): void
    {
        try {
            $this->qdrant->deletePointsByPostId($postId);
        } catch (Throwable) {
            // Collection có thể chưa tồn tại hoặc không còn point; vẫn dọn DB.
        }

        PostSemanticChunk::query()->where('post_id', $postId)->delete();
    }

    /**
     * Chunk → embed (document) → upsert Qdrant → ghi post_semantic_chunks.
     *
     * @throws RuntimeException
     */
    public function indexPost(Post $post): void
    {
        if (config('semantic_search.embedding.provider') !== 'local_http') {
            throw new RuntimeException('Indexing requires EMBEDDING_PROVIDER=local_http.');
        }

        $postId = (int) $post->id;
        $chunkRows = SemanticChunker::chunksForIndexing($post->title, (string) $post->content);

        $this->qdrant->deletePointsByPostId($postId);
        PostSemanticChunk::query()->where('post_id', $postId)->delete();

        if ($chunkRows === []) {
            return;
        }

        $modelName = (string) config('semantic_search.embedding.model');
        $dimensions = (int) config('semantic_search.embedding.dimensions');
        $embedBatch = max(1, min(128, (int) config('semantic_search.indexing.embed_chunk_batch', 32)));

        $titlePayload = mb_substr((string) ($post->title ?? ''), 0, 512);
        $url = (string) $post->url;
        $createdAt = $post->created_at instanceof Carbon
            ? $post->created_at->toIso8601String()
            : null;

        $prepared = [];
        foreach ($chunkRows as $row) {
            $prepared[] = [
                'chunk_index' => $row['chunk_index'],
                'text' => $row['text'],
                'content_hash' => hash('sha256', $row['text']),
                'vector_point_id' => $this->stablePointId($postId, $row['chunk_index']),
            ];
        }

        $now = now();

        foreach (array_chunk($prepared, $embedBatch) as $batch) {
            $texts = array_column($batch, 'text');
            $vectors = $this->embedding->embed($texts, 'document');
            if (count($vectors) !== count($batch)) {
                throw new RuntimeException('Embedding batch size mismatch.');
            }
            $dim = count($vectors[0]);
            if ($dim !== $dimensions) {
                throw new RuntimeException("Embedding vector length {$dim} does not match EMBEDDING_DIMENSIONS ({$dimensions}).");
            }

            $points = [];
            foreach ($batch as $i => $item) {
                $points[] = [
                    'id' => $item['vector_point_id'],
                    'vector' => $vectors[$i],
                    'payload' => [
                        'post_id' => $postId,
                        'chunk_index' => $item['chunk_index'],
                        'title' => $titlePayload,
                        'url' => $url,
                        'created_at' => $createdAt,
                    ],
                ];
            }

            $this->qdrant->upsertPoints($points);

            $insertRows = [];
            foreach ($batch as $item) {
                $insertRows[] = [
                    'post_id' => $postId,
                    'chunk_index' => $item['chunk_index'],
                    'chunk_text' => $item['text'],
                    'content_hash' => $item['content_hash'],
                    'embedding_model' => $modelName,
                    'embedding_dimensions' => $dimensions,
                    'vector_point_id' => $item['vector_point_id'],
                    'is_indexed' => true,
                    'indexed_at' => $now,
                    'last_error' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            PostSemanticChunk::query()->insert($insertRows);
        }
    }
}
