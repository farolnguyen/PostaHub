<?php

namespace App\Services\SemanticSearch;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class QdrantHttpClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey,
        private readonly int $timeoutSeconds,
    ) {}

    public static function fromConfig(): self
    {
        $q = config('semantic_search.qdrant');

        return new self(
            rtrim((string) ($q['base_url'] ?? 'http://127.0.0.1:6333'), '/'),
            isset($q['api_key']) && $q['api_key'] !== '' ? (string) $q['api_key'] : null,
            (int) ($q['timeout_seconds'] ?? 10),
        );
    }

    public function collectionName(): string
    {
        return (string) config('semantic_search.qdrant.collection', 'post_chunks');
    }

    public function collectionExists(string $name): bool
    {
        $response = $this->client()->get('/collections/'.rawurlencode($name));

        return $response->successful();
    }

    /**
     * @return int|null Vector size for default unnamed vector config
     */
    public function defaultVectorSize(string $name): ?int
    {
        $response = $this->client()->get('/collections/'.rawurlencode($name));
        if (! $response->successful()) {
            return null;
        }

        $vectors = data_get($response->json(), 'result.config.params.vectors');
        if (! is_array($vectors)) {
            return null;
        }
        if (isset($vectors['size'])) {
            return (int) $vectors['size'];
        }

        return null;
    }

    public function createCollection(string $name, int $vectorSize, string $distance = 'Cosine'): void
    {
        $response = $this->client()->put('/collections/'.rawurlencode($name), [
            'vectors' => [
                'size' => $vectorSize,
                'distance' => $distance,
            ],
        ]);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'Qdrant create collection failed: '.$response->body(),
                0,
                $e
            );
        }
    }

    /**
     * Create collection if missing. If it exists, assert vector size matches.
     */
    public function ensureCollection(int $expectedVectorSize): void
    {
        $name = $this->collectionName();

        if (! $this->collectionExists($name)) {
            $this->createCollection($name, $expectedVectorSize);

            return;
        }

        $size = $this->defaultVectorSize($name);
        if ($size === null) {
            throw new RuntimeException(
                "Qdrant collection [{$name}] exists but vector size could not be read. Check Qdrant version / config."
            );
        }

        if ($size !== $expectedVectorSize) {
            throw new RuntimeException(
                "Qdrant collection [{$name}] has vector size {$size}, expected {$expectedVectorSize}. Delete the collection or use a matching EMBEDDING_DIMENSIONS."
            );
        }
    }

    /**
     * @param  list<array{id: string, vector: list<float>, payload: array<string, mixed>}>  $points
     */
    public function upsertPoints(array $points): void
    {
        if ($points === []) {
            return;
        }

        $name = $this->collectionName();
        $response = $this->client()->put(
            '/collections/'.rawurlencode($name).'/points?wait=true',
            ['points' => $points]
        );

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'Qdrant upsert failed: '.$response->body(),
                0,
                $e
            );
        }
    }

    /**
     * @param  list<float>  $vector
     * @return list<array{id: string, score: float, payload: array<string, mixed>}>
     */
    public function searchVectors(array $vector, int $limit): array
    {
        $name = $this->collectionName();
        $response = $this->client()->post(
            '/collections/'.rawurlencode($name).'/points/search',
            [
                'vector' => $vector,
                'limit' => max(1, min(256, $limit)),
                'with_payload' => true,
                'with_vector' => false,
            ]
        );

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'Qdrant search failed: '.$response->body(),
                0,
                $e
            );
        }

        $hits = data_get($response->json(), 'result', []);
        if (! is_array($hits)) {
            return [];
        }

        $out = [];
        foreach ($hits as $hit) {
            if (! is_array($hit)) {
                continue;
            }
            $id = $hit['id'] ?? null;
            if ($id === null) {
                continue;
            }
            $score = isset($hit['score']) ? (float) $hit['score'] : 0.0;
            $payload = isset($hit['payload']) && is_array($hit['payload']) ? $hit['payload'] : [];
            $out[] = [
                'id' => (string) $id,
                'score' => $score,
                'payload' => $payload,
            ];
        }

        return $out;
    }

    public function deletePointsByPostId(int $postId): void
    {
        $name = $this->collectionName();
        $response = $this->client()->post(
            '/collections/'.rawurlencode($name).'/points/delete?wait=true',
            [
                'filter' => [
                    'must' => [
                        [
                            'key' => 'post_id',
                            'match' => ['value' => $postId],
                        ],
                    ],
                ],
            ]
        );

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'Qdrant delete points failed: '.$response->body(),
                0,
                $e
            );
        }
    }

    private function client(): PendingRequest
    {
        $http = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeoutSeconds)
            ->acceptJson();

        if ($this->apiKey !== null) {
            $http = $http->withHeaders(['api-key' => $this->apiKey]);
        }

        return $http;
    }
}
