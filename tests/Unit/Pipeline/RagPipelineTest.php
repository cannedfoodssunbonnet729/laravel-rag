<?php

declare(strict_types=1);

use Moneo\LaravelRag\Cache\EmbeddingCache;
use Moneo\LaravelRag\Pipeline\RagPipeline;
use Moneo\LaravelRag\Pipeline\RagResult;
use Moneo\LaravelRag\Search\HybridSearch;
use Moneo\LaravelRag\Search\Reranker;
use Moneo\LaravelRag\Support\PrismRetryHandler;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

function makePipeline(
    ?VectorStoreContract $store = null,
    ?EmbeddingCache $cache = null,
    ?HybridSearch $hybrid = null,
    ?Reranker $reranker = null,
    ?PrismRetryHandler $prism = null,
): RagPipeline {
    return new RagPipeline(
        vectorStore: $store ?? Mockery::mock(VectorStoreContract::class),
        embeddingCache: $cache ?? new EmbeddingCache(enabled: false),
        hybridSearch: $hybrid ?? Mockery::mock(HybridSearch::class),
        reranker: $reranker ?? new Reranker(enabled: false),
        prismRetryHandler: $prism,
    );
}

// === Builder / Immutability ===

test('from returns a clone', function () {
    $p = makePipeline();
    expect($p->from('X'))->not->toBe($p);
});

test('limit returns a clone', function () {
    expect(makePipeline()->limit(10))->not->toBe(makePipeline());
});

test('threshold returns a clone', function () {
    $p = makePipeline();
    expect($p->threshold(0.5))->not->toBe($p);
});

test('filter returns a clone', function () {
    $p = makePipeline();
    expect($p->filter(['k' => 'v']))->not->toBe($p);
});

test('systemPrompt returns a clone', function () {
    $p = makePipeline();
    expect($p->systemPrompt('p'))->not->toBe($p);
});

test('using returns a clone', function () {
    $p = makePipeline();
    expect($p->using('openai', 'gpt-4'))->not->toBe($p);
});

test('hybrid returns a clone', function () {
    $p = makePipeline();
    expect($p->hybrid())->not->toBe($p);
});

test('rerank returns a clone', function () {
    $p = makePipeline();
    expect($p->rerank(5))->not->toBe($p);
});

test('fluent methods are chainable', function () {
    $r = makePipeline()->from('X')->limit(10)->threshold(0.5)->filter([])->systemPrompt('p')->using('a', 'b')->hybrid()->rerank(5);
    expect($r)->toBeInstanceOf(RagPipeline::class);
});

test('agentic returns AgenticRetriever', function () {
    expect(makePipeline()->agentic(2))->toBeInstanceOf(\Moneo\LaravelRag\Agentic\AgenticRetriever::class);
});

// === Context Building ===

test('buildContext returns fallback for empty chunks', function () {
    $p = makePipeline();
    $m = (new ReflectionClass($p))->getMethod('buildContext');
    $m->setAccessible(true);
    expect($m->invoke($p, collect()))->toBe('No relevant context found.');
});

test('buildContext joins chunks with separator', function () {
    $p = makePipeline();
    $m = (new ReflectionClass($p))->getMethod('buildContext');
    $m->setAccessible(true);
    $chunks = collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'Chunk 1'],
        ['id' => '2', 'score' => 0.8, 'metadata' => [], 'content' => 'Chunk 2'],
    ]);
    expect($m->invoke($p, $chunks))->toContain('Chunk 1')->toContain('Chunk 2')->toContain('---');
});

test('resolveTable defaults to documents', function () {
    $p = makePipeline();
    $m = (new ReflectionClass($p))->getMethod('resolveTable');
    $m->setAccessible(true);
    expect($m->invoke($p))->toBe('documents');
});

// === embed() ===

test('embed calls PrismRetryHandler and returns vector', function () {
    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->once()->andReturn([0.1, 0.2, 0.3]);

    expect(makePipeline(prism: $prism)->embed('test'))->toBe([0.1, 0.2, 0.3]);
});

test('embed uses cache on hit', function () {
    $cache = Mockery::mock(EmbeddingCache::class)->makePartial();
    $cache->shouldReceive('get')->with('cached')->andReturn([0.5, 0.6]);
    $reflection = new ReflectionProperty(EmbeddingCache::class, 'enabled');
    $reflection->setValue($cache, true);

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldNotReceive('embed');

    expect(makePipeline(cache: $cache, prism: $prism)->embed('cached'))->toBe([0.5, 0.6]);
});

test('embed caches result on miss', function () {
    $cache = Mockery::mock(EmbeddingCache::class)->makePartial();
    $cache->shouldReceive('get')->andReturn(null);
    $cache->shouldReceive('put')->once();
    $reflection = new ReflectionProperty(EmbeddingCache::class, 'enabled');
    $reflection->setValue($cache, true);

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);

    makePipeline(cache: $cache, prism: $prism)->embed('new');
});

// === retrieve() ===

test('retrieve performs similarity search', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'Result'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);

    $chunks = makePipeline(store: $store, prism: $prism)->retrieve('q');
    expect($chunks)->toHaveCount(1)->and($chunks->first()['content'])->toBe('Result');
});

test('retrieve applies metadata filters', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => ['cat' => 'tech'], 'content' => 'Match'],
        ['id' => '2', 'score' => 0.8, 'metadata' => ['cat' => 'sports'], 'content' => 'No'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);

    $chunks = makePipeline(store: $store, prism: $prism)->filter(['cat' => 'tech'])->retrieve('q');
    expect($chunks)->toHaveCount(1)->and($chunks->first()['id'])->toBe('1');
});

test('retrieve respects limit', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'A'],
        ['id' => '2', 'score' => 0.8, 'metadata' => [], 'content' => 'B'],
        ['id' => '3', 'score' => 0.7, 'metadata' => [], 'content' => 'C'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);

    expect(makePipeline(store: $store, prism: $prism)->limit(2)->retrieve('q'))->toHaveCount(2);
});

test('retrieve uses hybrid search when enabled', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();

    $hybrid = Mockery::mock(HybridSearch::class);
    $hybrid->shouldReceive('search')->once()->andReturn(collect([
        ['id' => '1', 'score' => 0.95, 'metadata' => [], 'content' => 'Hybrid'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);

    $chunks = makePipeline(store: $store, hybrid: $hybrid, prism: $prism)->hybrid()->retrieve('q');
    expect($chunks->first()['content'])->toBe('Hybrid');
});

test('retrieve multiplies limit by 4 when reranking', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')
        ->withArgs(fn ($v, $limit) => $limit === 20)
        ->andReturn(collect());

    $reranker = Mockery::mock(Reranker::class);
    $reranker->shouldReceive('rerank')->andReturn(collect());

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);

    makePipeline(store: $store, reranker: $reranker, prism: $prism)->rerank(5)->retrieve('q');
});

// === ask() ===

test('ask returns RagResult with answer and chunks', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'Context'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);
    $prism->shouldReceive('generate')->andReturn('The answer is 42.');

    $result = makePipeline(store: $store, prism: $prism)->ask('What?');

    expect($result)->toBeInstanceOf(RagResult::class)
        ->and($result->answer)->toBe('The answer is 42.')
        ->and($result->question)->toBe('What?')
        ->and($result->chunks)->toHaveCount(1)
        ->and($result->retrievalTimeMs)->toBeGreaterThan(0);
});

test('askWithSources includes source in context', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => ['source' => 'doc.pdf'], 'content' => 'Src content'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);
    $prism->shouldReceive('generate')
        ->withArgs(fn ($p, $m, $sys, $q) => str_contains($sys, '[Source 0: doc.pdf]'))
        ->andReturn('With sources.');

    $result = makePipeline(store: $store, prism: $prism)->askWithSources('q');
    expect($result->answer)->toBe('With sources.');
});

test('dryRun retrieves without generating', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'Chunk'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);
    $prism->shouldNotReceive('generate');

    expect(makePipeline(store: $store, prism: $prism)->dryRun('q'))->toHaveCount(1);
});

test('stream returns RagStream', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect());

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);

    expect(makePipeline(store: $store, prism: $prism)->stream('q'))
        ->toBeInstanceOf(\Moneo\LaravelRag\Streaming\RagStream::class);
});
