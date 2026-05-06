<?php

namespace App\Services\SemanticSearch;

use App\Models\Post;
use App\Models\PostSemanticChunk;
use App\Support\SemanticChunker;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SemanticSearchService
{
    public function __construct(
        private readonly EmbeddingHttpClient $embedding,
        private readonly QdrantHttpClient $qdrant,
    ) {}

    /**
     * @return array{
     *     query: string,
     *     mode: string,
     *     fallback_reason: ?string,
     *     paginator: LengthAwarePaginator<int, object>
     * }
     */
    public function search(string $query, int $page = 1): array
    {
        $query = trim($query);
        $perPage = $this->resolvePerPage();
        $page = max(1, $page);

        if ($query === '') {
            return $this->emptyPaginator($query, $perPage, $page, 'empty', null);
        }

        $maxLen = (int) config('semantic_search.search.query_max_length', 500);
        if (mb_strlen($query) > $maxLen) {
            $query = mb_substr($query, 0, $maxLen);
        }

        if (mb_strlen($query) < 2) {
            return $this->emptyPaginator($query, $perPage, $page, 'keyword', 'Câu tìm kiếm quá ngắn (tối thiểu 2 ký tự).');
        }

        $useSemantic = (bool) config('semantic_search.enabled', false)
            && config('semantic_search.embedding.provider') === 'local_http';

        $keywordFallbackHint = null;

        if ($useSemantic) {
            try {
                $semantic = $this->searchSemantic($query, $perPage, $page);
                if ($semantic['total'] > 0) {
                    return [
                        'query' => $query,
                        'mode' => 'semantic',
                        'fallback_reason' => null,
                        'paginator' => $semantic['paginator'],
                    ];
                }

                if (! (bool) config('semantic_search.search.fallback_on_empty', true)) {
                    return [
                        'query' => $query,
                        'mode' => 'semantic',
                        'fallback_reason' => 'Không có kết quả ngữ nghĩa phù hợp.',
                        'paginator' => $semantic['paginator'],
                    ];
                }

                $keywordFallbackHint = 'Không có kết quả semantic phù hợp; hiển thị theo từ khóa.';
            } catch (Throwable $e) {
                Log::warning('Semantic search failed', ['message' => $e->getMessage()]);
                if (! (bool) config('semantic_search.search.fallback_on_error', true)) {
                    return $this->emptyPaginator($query, $perPage, $page, 'error', 'Semantic search tạm thời không khả dụng.');
                }

                $keywordFallbackHint = 'Semantic tạm lỗi ('.Str::limit($e->getMessage(), 120).'); hiển thị theo từ khóa.';
            }
        }

        $paginator = $this->searchKeywordPaginator($query, $perPage, $page);

        return [
            'query' => $query,
            'mode' => 'keyword',
            'fallback_reason' => $keywordFallbackHint,
            'paginator' => $paginator,
        ];
    }

    /**
     * @return array{total: int, paginator: LengthAwarePaginator<int, object>}
     */
    private function searchSemantic(string $query, int $perPage, int $page): array
    {
        $vectors = $this->embedding->embed([$query], 'query');
        $vector = $vectors[0] ?? [];
        if ($vector === []) {
            throw new RuntimeException('Empty query embedding.');
        }

        $limit = (int) config('semantic_search.search.vector_hits_limit', 80);
        $hits = $this->qdrant->searchVectors($vector, $limit);

        $bestByPost = [];
        foreach ($hits as $hit) {
            $postId = isset($hit['payload']['post_id']) ? (int) $hit['payload']['post_id'] : 0;
            if ($postId <= 0) {
                continue;
            }
            $chunkIndex = isset($hit['payload']['chunk_index']) ? (int) $hit['payload']['chunk_index'] : 0;
            $score = $hit['score'];
            if (! isset($bestByPost[$postId]) || $score > $bestByPost[$postId]['score']) {
                $bestByPost[$postId] = [
                    'score' => $score,
                    'chunk_index' => $chunkIndex,
                ];
            }
        }

        uasort($bestByPost, fn ($a, $b) => $b['score'] <=> $a['score']);
        $orderedIds = array_keys($bestByPost);
        $total = count($orderedIds);

        $slice = array_slice($orderedIds, ($page - 1) * $perPage, $perPage);
        $posts = Post::query()
            ->with(['user'])
            ->withCount(['likes', 'comments'])
            ->whereIn('id', $slice)
            ->get()
            ->keyBy('id');

        $snippetLen = (int) config('semantic_search.search.snippet_max_chars', 260);
        $items = collect($slice)
            ->map(function (int $id) use ($posts, $bestByPost, $snippetLen) {
                $post = $posts->get($id);
                if ($post === null) {
                    return null;
                }
                $meta = $bestByPost[$id];
                $snippet = $this->snippetForChunk($post, $meta['chunk_index'], $snippetLen);

                return (object) [
                    'post' => $post,
                    'snippet' => $snippet,
                    'score' => $meta['score'],
                    'chunk_index' => $meta['chunk_index'],
                ];
            })
            ->filter()
            ->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
        $paginator->appends(['q' => $query]);

        return ['total' => $total, 'paginator' => $paginator];
    }

    private function searchKeywordPaginator(string $query, int $perPage, int $page): LengthAwarePaginator
    {
        $terms = $this->keywordTerms($query);
        if ($terms->isEmpty()) {
            return $this->emptyPaginator($query, $perPage, $page, 'keyword', null)['paginator'];
        }

        $base = Post::query()
            ->with(['user'])
            ->withCount(['likes', 'comments'])
            ->where(function ($q) use ($terms): void {
                foreach ($terms as $t) {
                    $like = '%'.$this->escapeLike($t).'%';
                    $q->orWhere(fn ($w) => $w->where('title', 'like', $like)->orWhere('content', 'like', $like));
                }
            })
            ->latest();

        $paginator = $base->paginate($perPage, ['*'], 'page', $page);
        $paginator->appends(['q' => $query]);

        $snippetLen = (int) config('semantic_search.search.snippet_max_chars', 260);
        $paginator->getCollection()->transform(function (Post $post) use ($snippetLen) {
            return (object) [
                'post' => $post,
                'snippet' => mb_substr(SemanticChunker::normalizeText(strip_tags((string) $post->content)), 0, $snippetLen),
                'score' => null,
                'chunk_index' => null,
            ];
        });

        return $paginator;
    }

    private function snippetForChunk(Post $post, int $chunkIndex, int $maxChars): string
    {
        $text = PostSemanticChunk::query()
            ->where('post_id', $post->id)
            ->where('chunk_index', $chunkIndex)
            ->value('chunk_text');

        if (is_string($text) && $text !== '') {
            return mb_substr(SemanticChunker::normalizeText($text), 0, $maxChars);
        }

        return mb_substr(SemanticChunker::normalizeText(strip_tags((string) $post->content)), 0, $maxChars);
    }

    /**
     * @return Collection<int, string>
     */
    private function keywordTerms(string $query): Collection
    {
        $parts = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);

        return collect(is_array($parts) ? $parts : [])
            ->map(fn (string $t) => mb_substr(trim($t), 0, 80))
            ->filter(fn (string $t) => mb_strlen($t) >= 2)
            ->unique()
            ->take(10)
            ->values();
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function resolvePerPage(): int
    {
        $default = (int) config('semantic_search.search.per_page_default', 15);
        $max = (int) config('semantic_search.search.per_page_max', 50);
        $p = (int) request()->input('per_page', $default);

        return max(1, min($max, $p > 0 ? $p : $default));
    }

    /**
     * @return array{query: string, mode: string, fallback_reason: ?string, paginator: LengthAwarePaginator<int, object>}
     */
    private function emptyPaginator(string $query, int $perPage, int $page, string $mode, ?string $reason): array
    {
        $empty = new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );
        $empty->appends(array_filter(['q' => $query]));

        return [
            'query' => $query,
            'mode' => $mode,
            'fallback_reason' => $reason,
            'paginator' => $empty,
        ];
    }
}
