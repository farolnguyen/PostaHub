<?php

namespace App\Support;

class SemanticChunker
{
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

