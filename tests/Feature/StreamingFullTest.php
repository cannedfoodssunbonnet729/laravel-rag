<?php

declare(strict_types=1);

use Moneo\LaravelRag\Cache\EmbeddingCache;
use Moneo\LaravelRag\Pipeline\RagPipeline;
use Moneo\LaravelRag\Search\HybridSearch;
use Moneo\LaravelRag\Search\Reranker;
use Moneo\LaravelRag\Streaming\RagStream;
use Moneo\LaravelRag\Support\PrismRetryHandler;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

test('pipeline stream() returns RagStream with correct data', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => ['source' => 'doc.md'], 'content' => 'Context about RAG.'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1, 0.2]);

    $pipeline = new RagPipeline(
        vectorStore: $store,
        embeddingCache: new EmbeddingCache(enabled: false),
        hybridSearch: new HybridSearch($store),
        reranker: new Reranker(enabled: false),
        prismRetryHandler: $prism,
    );

    $stream = $pipeline->stream('What is RAG?');

    expect($stream)->toBeInstanceOf(RagStream::class);

    $response = $stream->toStreamedResponse();
    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toBe('text/event-stream');
});

test('RagStream getSources handles empty and full chunks', function () {
    $stream = new RagStream(
        question: 'Q?',
        context: 'C',
        chunks: collect([
            ['id' => '1', 'score' => 0.95, 'metadata' => ['source' => 'a.md'], 'content' => 'First'],
            ['id' => '2', 'score' => 0.80, 'metadata' => [], 'content' => str_repeat('x', 500)],
        ]),
        systemPrompt: 'Custom prompt.',
        provider: 'openai',
        model: 'gpt-4o',
    );

    $ref = new ReflectionClass($stream);
    $m = $ref->getMethod('getSources');
    $m->setAccessible(true);
    $sources = $m->invoke($stream);

    expect($sources)->toHaveCount(2)
        ->and($sources[0]['source'])->toBe('a.md')
        ->and($sources[1]['source'])->toBe('Unknown')
        ->and(strlen($sources[1]['preview']))->toBe(200);
});
