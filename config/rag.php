<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Vector Store
    |--------------------------------------------------------------------------
    |
    | Supported: "pgvector", "sqlite-vec"
    | Community drivers: moneo/laravel-rag-{driver}
    |
    */
    'vector_store' => env('RAG_VECTOR_STORE', 'pgvector'),

    'stores' => [
        'pgvector' => [
            'driver' => 'pgvector',
            'connection' => 'pgsql',
        ],
        'sqlite-vec' => [
            'driver' => 'sqlite-vec',
            'database' => env('RAG_SQLITE_DATABASE', database_path('vector.sqlite')),
            'extension' => env('RAG_SQLITE_VEC_EXTENSION'), // null = auto (vec0.so from sqlite3.extension_dir)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedding Configuration
    |--------------------------------------------------------------------------
    */
    'embedding' => [
        'driver' => env('RAG_EMBEDDING_DRIVER', 'openai'),
        'model' => env('RAG_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'dimensions' => (int) env('RAG_EMBEDDING_DIMENSIONS', 1536),
        'cache' => (bool) env('RAG_EMBEDDING_CACHE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | LLM Configuration
    |--------------------------------------------------------------------------
    */
    'llm' => [
        'provider' => env('RAG_LLM_PROVIDER', 'openai'),
        'model' => env('RAG_LLM_MODEL', 'gpt-4o'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Defaults
    |--------------------------------------------------------------------------
    */
    'search' => [
        'default_limit' => 5,
        'default_distance' => 'cosine',
        'rrf_k' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default System Prompt
    |--------------------------------------------------------------------------
    |
    | The default system prompt sent to the LLM. Override per-request with
    | ->systemPrompt(). Set via RAG_SYSTEM_PROMPT in .env.
    |
    */
    'system_prompt' => env('RAG_SYSTEM_PROMPT', 'You are a helpful assistant. Answer the question based on the provided context. If the context does not contain enough information, say so.'),

    /*
    |--------------------------------------------------------------------------
    | Ingest Pipeline
    |--------------------------------------------------------------------------
    */
    'ingest' => [
        'chunk_strategy' => 'character',
        'chunk_size' => 500,
        'chunk_overlap' => 50,
        'async' => (bool) env('RAG_ASYNC', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reranker
    |--------------------------------------------------------------------------
    */
    'reranker' => [
        'enabled' => false,
        'top_k' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Agentic RAG
    |--------------------------------------------------------------------------
    */
    'agentic' => [
        'max_steps' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversation Memory
    |--------------------------------------------------------------------------
    */
    'memory' => [
        'max_tokens' => 4000,
        'summary_threshold' => 0.8,
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Server
    |--------------------------------------------------------------------------
    */
    'mcp' => [
        'enabled' => (bool) env('RAG_MCP', false),
        'port' => (int) env('RAG_MCP_PORT', 3000),
    ],

];
