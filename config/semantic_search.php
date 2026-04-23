<?php

return [
    'enabled' => (bool) env('SEMANTIC_SEARCH_ENABLED', false),

    'qdrant' => [
        'base_url' => env('QDRANT_URL', 'http://127.0.0.1:6333'),
        'api_key' => env('QDRANT_API_KEY'),
        'collection' => env('QDRANT_COLLECTION', 'post_chunks'),
        'timeout_seconds' => (int) env('QDRANT_TIMEOUT_SECONDS', 10),
    ],

    'embedding' => [
        'provider' => env('EMBEDDING_PROVIDER', 'openai'),
        'model' => env('EMBEDDING_MODEL', 'text-embedding-3-small'),
        'dimensions' => (int) env('EMBEDDING_DIMENSIONS', 1536),
        'api_key' => env('EMBEDDING_API_KEY'),
        'timeout_seconds' => (int) env('EMBEDDING_TIMEOUT_SECONDS', 15),
    ],

    'indexing' => [
        'chunk_size_chars' => (int) env('SEMANTIC_CHUNK_SIZE_CHARS', 1200),
        'chunk_overlap_chars' => (int) env('SEMANTIC_CHUNK_OVERLAP_CHARS', 200),
        'batch_size' => (int) env('SEMANTIC_INDEX_BATCH_SIZE', 100),
    ],
];

