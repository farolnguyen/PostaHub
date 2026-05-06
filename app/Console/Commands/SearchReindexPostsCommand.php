<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\SemanticSearch\PostSemanticIndexer;
use App\Services\SemanticSearch\QdrantHttpClient;
use Illuminate\Console\Command;
use Throwable;

class SearchReindexPostsCommand extends Command
{
    protected $signature = 'search:reindex-posts
                            {--post= : Chỉ index đúng một post_id}
                            {--chunk=100 : Số bài ghi mỗi lần query khi index toàn bộ}
                            {--from-id=0 : Chỉ lấy post có id lớn hơn giá trị này}';

    protected $description = 'Semantic index: chunk → embed → Qdrant + bảng post_semantic_chunks';

    public function handle(PostSemanticIndexer $indexer, QdrantHttpClient $qdrant): int
    {
        if (config('semantic_search.embedding.provider') !== 'local_http') {
            $this->error('Cần EMBEDDING_PROVIDER=local_http và embedding-service đang chạy.');

            return self::FAILURE;
        }

        $expectedDims = (int) config('semantic_search.embedding.dimensions', 384);
        try {
            $qdrant->ensureCollection($expectedDims);
        } catch (Throwable $e) {
            $this->error('Qdrant: '.$e->getMessage());

            return self::FAILURE;
        }

        $single = $this->option('post');
        if ($single !== null && $single !== '') {
            $post = Post::query()->find((int) $single);
            if ($post === null) {
                $this->error('Không tìm thấy post.');

                return self::FAILURE;
            }

            return $this->indexOne($indexer, $post);
        }

        $chunk = max(1, (int) $this->option('chunk'));
        $fromId = max(0, (int) $this->option('from-id'));

        $total = Post::query()->when($fromId > 0, fn ($q) => $q->where('id', '>', $fromId))->count();
        $this->info("Posts to process: {$total} (chunk={$chunk}, from-id={$fromId}).");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $errors = 0;
        Post::query()
            ->when($fromId > 0, fn ($q) => $q->where('id', '>', $fromId))
            ->orderBy('id')
            ->chunkById($chunk, function ($posts) use ($indexer, $bar, &$errors) {
                foreach ($posts as $post) {
                    try {
                        $indexer->indexPost($post);
                    } catch (Throwable $e) {
                        $errors++;
                        $this->newLine();
                        $this->error("Post {$post->id}: ".$e->getMessage());
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        if ($errors > 0) {
            $this->warn("Hoàn tất với {$errors} lỗi.");

            return self::FAILURE;
        }

        $this->info('Reindex hoàn tất.');

        return self::SUCCESS;
    }

    private function indexOne(PostSemanticIndexer $indexer, Post $post): int
    {
        try {
            $indexer->indexPost($post);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Đã index post_id={$post->id}.");

        return self::SUCCESS;
    }
}
