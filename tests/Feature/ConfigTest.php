<?php

declare(strict_types=1);

test('rag config has all expected keys', function () {
    expect(config('rag'))->toHaveKeys([
        'vector_store', 'stores', 'embedding', 'llm',
        'search', 'ingest', 'reranker', 'agentic', 'memory', 'mcp',
    ]);
});

test('embedding config has required fields', function () {
    expect(config('rag.embedding'))->toHaveKeys(['driver', 'model', 'dimensions', 'cache']);
});

test('stores config has both drivers', function () {
    expect(config('rag.stores'))->toHaveKeys(['pgvector', 'sqlite-vec']);
});

test('search defaults are reasonable', function () {
    expect(config('rag.search.default_limit'))->toBe(5)
        ->and(config('rag.search.default_distance'))->toBe('cosine')
        ->and(config('rag.search.rrf_k'))->toBe(60);
});

test('ingest defaults are reasonable', function () {
    expect(config('rag.ingest.chunk_strategy'))->toBe('character')
        ->and(config('rag.ingest.chunk_size'))->toBe(500)
        ->and(config('rag.ingest.chunk_overlap'))->toBe(50);
});

test('reranker defaults to disabled', function () {
    expect(config('rag.reranker.enabled'))->toBeFalse();
});

test('mcp defaults to disabled', function () {
    expect(config('rag.mcp.enabled'))->toBeFalse();
});

test('memory has token limit', function () {
    expect(config('rag.memory.max_tokens'))->toBe(4000);
});

test('default config passes validation', function () {
    // The app booted successfully with default config — validation passed
    expect(config('rag.embedding.dimensions'))->toBeGreaterThan(0)
        ->and(config('rag.ingest.chunk_size'))->toBeGreaterThan(0)
        ->and(config('rag.ingest.chunk_overlap'))->toBeLessThan(config('rag.ingest.chunk_size'));
});
