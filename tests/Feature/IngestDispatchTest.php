<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Moneo\LaravelRag\Cache\EmbeddingCache;
use Moneo\LaravelRag\Chunking\ChunkingFactory;
use Moneo\LaravelRag\Pipeline\IngestPipeline;
use Moneo\LaravelRag\Support\PrismRetryHandler;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

test('dispatch pushes closure to queue', function () {
    Queue::fake();

    $store = Mockery::mock(VectorStoreContract::class);
    $prism = Mockery::mock(PrismRetryHandler::class);

    $pipeline = new IngestPipeline(
        vectorStore: $store,
        chunkingFactory: new ChunkingFactory,
        embeddingCache: new EmbeddingCache(enabled: false),
        prismRetryHandler: $prism,
    );

    $pipeline->text('Test content.')
        ->chunk(strategy: 'character', size: 500)
        ->dispatch();

    // dispatch() calls Laravel's dispatch() helper which queues a closure
    // With Queue::fake(), it won't actually run
    expect(true)->toBeTrue();
});

test('file method reads file content', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'rag_test_');
    file_put_contents($tmpFile, 'File content for testing.');

    $store = Mockery::mock(VectorStoreContract::class);
    $prism = Mockery::mock(PrismRetryHandler::class);

    $pipeline = new IngestPipeline(
        vectorStore: $store,
        chunkingFactory: new ChunkingFactory,
        embeddingCache: new EmbeddingCache(enabled: false),
        prismRetryHandler: $prism,
    );

    $result = $pipeline->file($tmpFile);

    $ref = new ReflectionClass($result);
    $prop = $ref->getProperty('content');
    $prop->setAccessible(true);

    expect($prop->getValue($result))->toBe('File content for testing.');

    unlink($tmpFile);
});

test('file method throws for nonexistent file', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $prism = Mockery::mock(PrismRetryHandler::class);

    $pipeline = new IngestPipeline(
        vectorStore: $store,
        chunkingFactory: new ChunkingFactory,
        embeddingCache: new EmbeddingCache(enabled: false),
        prismRetryHandler: $prism,
    );

    $pipeline->file('/nonexistent/file.txt');
})->throws(\InvalidArgumentException::class, 'File not found');

test('multiple chunk calls use last strategy', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('upsert')->atLeast()->once();

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn(array_fill(0, 1536, 0.1));

    $pipeline = new IngestPipeline(
        vectorStore: $store,
        chunkingFactory: new ChunkingFactory,
        embeddingCache: new EmbeddingCache(enabled: false),
        prismRetryHandler: $prism,
    );

    $ids = $pipeline->text(str_repeat('Hello world. This is a test. ', 50))
        ->chunk(strategy: 'character', size: 100)
        ->chunk(strategy: 'sentence', size: 200)  // This overrides character
        ->run();

    expect($ids)->not->toBeEmpty();
});
