<?php

return [
    'enabled' => (bool) env('SEMANTIC_SEARCH_ENABLED', false),

    'qdrant' => [
        'base_url' => env('QDRANT_URL', 'http://127.0.0.1:6333'),
        'api_key' => env('QDRANT_API_KEY'),
        'collection' => env('QDRANT_COLLECTION', 'post_chunks'),
        'timeout_seconds' => (int) env('QDRANT_TIMEOUT_SECONDS', 10),
    ],

    /*
    | Provider "local_http" = Python embedding-service (Task 2). Other providers reserved.
    */
    'embedding' => [
        'provider' => env('EMBEDDING_PROVIDER', 'local_http'),
        'model' => env('EMBEDDING_MODEL', 'intfloat/multilingual-e5-small'),
        'dimensions' => (int) env('EMBEDDING_DIMENSIONS', 384),
        'http' => [
            'base_url' => rtrim(env('EMBEDDING_HTTP_BASE_URL', 'http://127.0.0.1:8001'), '/'),
            'timeout_seconds' => (int) env('EMBEDDING_TIMEOUT_SECONDS', 30),
        ],
        'api_key' => env('EMBEDDING_API_KEY'),
    ],

    'indexing' => [
        'chunk_size_chars' => (int) env('SEMANTIC_CHUNK_SIZE_CHARS', 1200),
        'chunk_overlap_chars' => (int) env('SEMANTIC_CHUNK_OVERLAP_CHARS', 200),
        'batch_size' => (int) env('SEMANTIC_INDEX_BATCH_SIZE', 100),
        'embed_chunk_batch' => (int) env('SEMANTIC_EMBED_CHUNK_BATCH', 32),
    ],

    'queue' => [
        'interactive' => env('SEMANTIC_QUEUE_INTERACTIVE', 'semantic'),
        'bulk' => env('SEMANTIC_QUEUE_BULK', 'semantic-bulk'),
        'bulk_reindex_batch_posts' => (int) env('SEMANTIC_BULK_REINDEX_BATCH_POSTS', 40),
        'bulk_reindex_delay_seconds' => (int) env('SEMANTIC_BULK_REINDEX_DELAY_SECONDS', 12),
    ],

    'search' => [
        'vector_hits_limit' => (int) env('SEMANTIC_SEARCH_VECTOR_HITS_LIMIT', 80),
        'per_page_default' => (int) env('SEMANTIC_SEARCH_PER_PAGE', 15),
        'per_page_max' => (int) env('SEMANTIC_SEARCH_PER_PAGE_MAX', 50),
        'query_max_length' => (int) env('SEMANTIC_SEARCH_QUERY_MAX', 500),
        'snippet_max_chars' => (int) env('SEMANTIC_SEARCH_SNIPPET_CHARS', 260),
        'fallback_on_empty' => (bool) filter_var(env('SEMANTIC_SEARCH_FALLBACK_ON_EMPTY', true), FILTER_VALIDATE_BOOL),
        'fallback_on_error' => (bool) filter_var(env('SEMANTIC_SEARCH_FALLBACK_ON_ERROR', true), FILTER_VALIDATE_BOOL),
    ],
];
