<?php

namespace App\Support;

class SemanticChunker
{
    /**
     * Tiêu đề một chunk riêng (index 0), nội dung HTML chunk tiếp theo.
     *
     * @return list<array{chunk_index: int, text: string}>
     */
    public static function chunksForIndexing(?string $title, string $contentHtml): array
    {
        $rows = [];
        $index = 0;
        $head = self::normalizeText((string) $title);
        if ($head !== '') {
            $rows[] = ['chunk_index' => $index, 'text' => $head];
            $index++;
        }
        foreach (self::chunkFromHtml($contentHtml) as $part) {
            $rows[] = ['chunk_index' => $index, 'text' => $part];
            $index++;
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    public static function chunkFromHtml(string $html): array
    {
        $cleanText = self::normalizeText(strip_tags($html));

        if ($cleanText === '') {
            return [];
        }

        $chunkSize = max(300, (int) config('semantic_search.indexing.chunk_size_chars', 1200));
        $overlap = max(0, (int) config('semantic_search.indexing.chunk_overlap_chars', 200));

        $chunks = [];
        $start = 0;
        $length = mb_strlen($cleanText);

        while ($start < $length) {
            $chunk = mb_substr($cleanText, $start, $chunkSize);
            $chunk = trim($chunk);

            if ($chunk !== '') {
                $chunks[] = $chunk;
            }

            if ($start + $chunkSize >= $length) {
                break;
            }

            $start += max(1, $chunkSize - $overlap);
        }

        return $chunks;
    }

    public static function normalizeText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
