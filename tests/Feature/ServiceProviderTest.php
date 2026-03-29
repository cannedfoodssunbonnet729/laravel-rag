<?php

declare(strict_types=1);

use Moneo\LaravelRag\Cache\EmbeddingCache;
use Moneo\LaravelRag\Chunking\ChunkingFactory;
use Moneo\LaravelRag\Pipeline\RagPipeline;
use Moneo\LaravelRag\Search\HybridSearch;
use Moneo\LaravelRag\Search\Reranker;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

test('registers vector store contract', function () {
    expect(app(VectorStoreContract::class))->toBeInstanceOf(VectorStoreContract::class);
});

test('registers embedding cache', function () {
    expect(app(EmbeddingCache::class))->toBeInstanceOf(EmbeddingCache::class);
});

test('registers chunking factory', function () {
    expect(app(ChunkingFactory::class))->toBeInstanceOf(ChunkingFactory::class);
});

test('registers hybrid search', function () {
    expect(app(HybridSearch::class))->toBeInstanceOf(HybridSearch::class);
});

test('registers reranker', function () {
    expect(app(Reranker::class))->toBeInstanceOf(Reranker::class);
});

test('rag pipeline is resolvable via facade accessor', function () {
    expect(app('rag.pipeline'))->toBeInstanceOf(RagPipeline::class);
});

test('ingest pipeline is resolvable', function () {
    expect(app('rag.ingest'))->toBeInstanceOf(\Moneo\LaravelRag\Pipeline\IngestPipeline::class);
});

test('rag eval is resolvable', function () {
    expect(app('rag.eval'))->toBeInstanceOf(\Moneo\LaravelRag\Evals\RagEval::class);
});

test('config is merged correctly', function () {
    expect(config('rag.vector_store'))->toBe('sqlite-vec')
        ->and(config('rag.search.default_limit'))->toBe(5)
        ->and(config('rag.search.rrf_k'))->toBe(60);
});

test('PrismRetryHandler is registered as singleton', function () {
    $a = app(\Moneo\LaravelRag\Support\PrismRetryHandler::class);
    $b = app(\Moneo\LaravelRag\Support\PrismRetryHandler::class);

    expect($a)->toBe($b);
});

test('RagLogger is registered as singleton', function () {
    $a = app(\Moneo\LaravelRag\Support\RagLogger::class);
    $b = app(\Moneo\LaravelRag\Support\RagLogger::class);

    expect($a)->toBe($b);
});
