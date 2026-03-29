<?php

declare(strict_types=1);

use Moneo\LaravelRag\Cache\EmbeddingCache;
use Moneo\LaravelRag\Chunking\ChunkingFactory;
use Moneo\LaravelRag\Chunking\Strategies\CharacterChunker;
use Moneo\LaravelRag\Pipeline\IngestPipeline;
use Moneo\LaravelRag\Support\PrismRetryHandler;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

function makeIngestPipeline(
    ?VectorStoreContract $store = null,
    ?ChunkingFactory $factory = null,
    ?EmbeddingCache $cache = null,
    ?PrismRetryHandler $prism = null,
): IngestPipeline {
    return new IngestPipeline(
        vectorStore: $store ?? Mockery::mock(VectorStoreContract::class),
        chunkingFactory: $factory ?? new ChunkingFactory,
        embeddingCache: $cache ?? new EmbeddingCache(enabled: false),
        prismRetryHandler: $prism,
    );
}

test('file throws for nonexistent path', function () {
    $pipeline = makeIngestPipeline();
    $pipeline->file('/nonexistent/path/document.txt');
})->throws(\InvalidArgumentException::class, 'File not found');

test('text sets content', function () {
    $pipeline = makeIngestPipeline();
    $result = $pipeline->text('Hello world');

    expect($result)->toBeInstanceOf(IngestPipeline::class);
});

test('chunk adds chunking step', function () {
    $pipeline = makeIngestPipeline();
    $result = $pipeline->chunk(strategy: 'character', size: 100, overlap: 10);

    expect($result)->toBeInstanceOf(IngestPipeline::class);
});

test('storeIn sets target model', function () {
    $pipeline = makeIngestPipeline();
    $result = $pipeline->storeIn('App\\Models\\Document');

    expect($result)->toBeInstanceOf(IngestPipeline::class);
});

test('withMetadata merges metadata', function () {
    $pipeline = makeIngestPipeline();
    $result = $pipeline->withMetadata(['key' => 'value']);

    expect($result)->toBeInstanceOf(IngestPipeline::class);
});

test('processChunks returns empty for null content', function () {
    $pipeline = makeIngestPipeline();

    $reflection = new ReflectionClass($pipeline);
    $method = $reflection->getMethod('processChunks');
    $method->setAccessible(true);

    expect($method->invoke($pipeline))->toBeEmpty();
});

test('processChunks uses default strategy when none specified', function () {
    config(['rag.ingest.chunk_strategy' => 'character', 'rag.ingest.chunk_size' => 100, 'rag.ingest.chunk_overlap' => 10]);

    $pipeline = makeIngestPipeline();
    $pipeline->text(str_repeat('a', 250));

    $reflection = new ReflectionClass($pipeline);
    $method = $reflection->getMethod('processChunks');
    $method->setAccessible(true);

    $chunks = $method->invoke($pipeline);

    expect($chunks)->not->toBeEmpty();
});

test('resolveTable defaults to documents', function () {
    $pipeline = makeIngestPipeline();

    $reflection = new ReflectionClass($pipeline);
    $method = $reflection->getMethod('resolveTable');
    $method->setAccessible(true);

    expect($method->invoke($pipeline))->toBe('documents');
});

test('fluent methods are chainable', function () {
    $pipeline = makeIngestPipeline();

    $result = $pipeline
        ->text('content')
        ->chunk(strategy: 'character', size: 100)
        ->withMetadata(['key' => 'val'])
        ->storeIn('App\\Models\\Doc');

    expect($result)->toBeInstanceOf(IngestPipeline::class);
});
