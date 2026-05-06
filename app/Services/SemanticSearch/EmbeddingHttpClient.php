<?php

namespace App\Services\SemanticSearch;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EmbeddingHttpClient
{
    public function health(): array
    {
        $response = $this->http()->get($this->baseUrl().'/health');
        $response->throw();

        return $response->json();
    }

    /**
     * @param  list<string>  $inputs
     * @return list<list<float>>
     */
    public function embed(array $inputs, string $inputType = 'document'): array
    {
        if ($inputs === []) {
            throw new RuntimeException('Embedding inputs must not be empty.');
        }

        $response = $this->http()->post($this->baseUrl().'/v1/embed', [
            'inputs' => $inputs,
            'input_type' => $inputType === 'query' ? 'query' : 'document',
        ]);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'Embedding request failed: '.$response->body(),
                0,
                $e
            );
        }

        $json = $response->json();
        if (! is_array($json) || ! isset($json['vectors']) || ! is_array($json['vectors'])) {
            throw new RuntimeException('Invalid embedding response shape.');
        }

        return $json['vectors'];
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('semantic_search.embedding.http.base_url'), '/');
    }

    private function http(): PendingRequest
    {
        $seconds = (int) config('semantic_search.embedding.http.timeout_seconds', 30);

        return Http::timeout($seconds)->acceptJson();
    }
}
