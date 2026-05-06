<?php

namespace App\Console\Commands;

use App\Services\SemanticSearch\EmbeddingHttpClient;
use App\Services\SemanticSearch\QdrantHttpClient;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class SemanticEnsureInfrastructureCommand extends Command
{
    protected $signature = 'semantic:ensure-infrastructure
                            {--skip-embedding : Only ensure Qdrant collection}
                            {--skip-qdrant : Only ping embedding service}';

    protected $description = 'Verify embedding HTTP service and ensure Qdrant collection exists with correct vector size';

    public function handle(EmbeddingHttpClient $embedding, QdrantHttpClient $qdrant): int
    {
        $provider = (string) config('semantic_search.embedding.provider', 'local_http');
        $expectedDims = (int) config('semantic_search.embedding.dimensions', 384);

        if (! $this->option('skip-embedding')) {
            if ($provider !== 'local_http') {
                $this->error("Embedding provider [{$provider}] is not supported. Use EMBEDDING_PROVIDER=local_http for Task 2.");

                return self::FAILURE;
            }

            try {
                $health = $embedding->health();
            } catch (Throwable $e) {
                $this->error('Embedding service unreachable: '.$e->getMessage());

                return self::FAILURE;
            }

            $status = $health['status'] ?? null;
            if ($status !== 'ok') {
                $this->warn('Embedding /health status is not "ok" yet (model may still be loading). Response: '.json_encode($health));

                return self::FAILURE;
            }

            $dims = isset($health['dimensions']) ? (int) $health['dimensions'] : 0;
            if ($dims !== $expectedDims) {
                $this->error("Embedding dimensions mismatch: service reports {$dims}, config expects {$expectedDims}.");

                return self::FAILURE;
            }

            $this->info("Embedding OK (model {$health['model']}, dimensions {$dims}).");
        }

        if ($this->option('skip-qdrant')) {
            return self::SUCCESS;
        }

        try {
            $qdrant->ensureCollection($expectedDims);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error('Qdrant error: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Qdrant collection ['.$qdrant->collectionName()."] ready (vector size {$expectedDims}).");

        return self::SUCCESS;
    }
}
