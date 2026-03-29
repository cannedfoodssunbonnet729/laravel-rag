<?php

declare(strict_types=1);

use Moneo\LaravelRag\Cache\EmbeddingCache;
use Moneo\LaravelRag\Facades\Rag;
use Moneo\LaravelRag\Pipeline\RagPipeline;
use Moneo\LaravelRag\Pipeline\RagResult;
use Moneo\LaravelRag\Search\HybridSearch;
use Moneo\LaravelRag\Search\Reranker;
use Moneo\LaravelRag\Support\PrismRetryHandler;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

/**
 * Full RAG pipeline integration tests.
 *
 * These test the complete flow: embed → search → generate
 * with mocked external dependencies (Prism, VectorStore).
 */

test('full ask flow: embed query → search → generate answer', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->with('documents')->andReturnSelf();
    $store->shouldReceive('similaritySearch')
        ->withArgs(fn ($vector, $limit) => $vector === [0.1, 0.2, 0.3] && $limit === 5)
        ->andReturn(collect([
            ['id' => 'chunk-1', 'score' => 0.95, 'metadata' => ['source' => 'guide.md'], 'content' => 'pgvector is a PostgreSQL extension'],
            ['id' => 'chunk-2', 'score' => 0.87, 'metadata' => ['source' => 'faq.md'], 'content' => 'It supports cosine distance'],
        ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')
        ->with('What is pgvector?', 'openai', 'text-embedding-3-small', 1536)
        ->andReturn([0.1, 0.2, 0.3]);
    $prism->shouldReceive('generate')
        ->withArgs(function ($provider, $model, $systemPrompt, $question) {
            return $provider === 'openai'
                && str_contains($systemPrompt, 'pgvector is a PostgreSQL extension')
                && str_contains($systemPrompt, 'cosine distance')
                && str_contains($question, 'pgvector'); // sanitised question
        })
        ->andReturn('pgvector is a PostgreSQL extension for vector similarity search supporting cosine distance.');

    $pipeline = new RagPipeline(
        vectorStore: $store,
        embeddingCache: new EmbeddingCache(enabled: false),
        hybridSearch: new HybridSearch($store),
        reranker: new Reranker(enabled: false),
        prismRetryHandler: $prism,
    );

    $result = $pipeline->ask('What is pgvector?');

    expect($result)->toBeInstanceOf(RagResult::class)
        ->and($result->answer)->toContain('pgvector')
        ->and($result->question)->toBe('What is pgvector?')
        ->and($result->chunks)->toHaveCount(2)
        ->and($result->chunks->first()['score'])->toBe(0.95)
        ->and($result->retrievalTimeMs)->toBeGreaterThan(0)
        ->and($result->generationTimeMs)->toBeGreaterThan(0)
        ->and($result->totalTimeMs())->toBeGreaterThan(0);
});

test('full askWithSources flow propagates sources in context', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => ['source' => 'docs/install.md'], 'content' => 'Run composer require'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);
    $prism->shouldReceive('generate')
        ->withArgs(fn ($p, $m, $sys, $q) => str_contains($sys, '[Source 0: docs/install.md]'))
        ->andReturn('Run composer require to install.');

    $pipeline = new RagPipeline(
        vectorStore: $store,
        embeddingCache: new EmbeddingCache(enabled: false),
        hybridSearch: new HybridSearch($store),
        reranker: new Reranker(enabled: false),
        prismRetryHandler: $prism,
    );

    $result = $pipeline->askWithSources('How to install?');
    $sources = $result->sources();

    expect($sources)->toHaveCount(1)
        ->and($sources->first()['source'])->toBe('docs/install.md')
        ->and($sources->first()['score'])->toBe(0.9);
});

test('full flow with metadata filter', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => ['lang' => 'en'], 'content' => 'English doc'],
        ['id' => '2', 'score' => 0.85, 'metadata' => ['lang' => 'tr'], 'content' => 'Turkish doc'],
        ['id' => '3', 'score' => 0.8, 'metadata' => ['lang' => 'en'], 'content' => 'Another English'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);
    $prism->shouldReceive('generate')->andReturn('English answer.');

    $pipeline = new RagPipeline(
        vectorStore: $store,
        embeddingCache: new EmbeddingCache(enabled: false),
        hybridSearch: new HybridSearch($store),
        reranker: new Reranker(enabled: false),
        prismRetryHandler: $prism,
    );

    $result = $pipeline->filter(['lang' => 'en'])->ask('Question?');

    expect($result->chunks)->toHaveCount(2)
        ->and($result->chunks->pluck('id')->toArray())->toBe(['1', '3']);
});

test('full flow with custom system prompt', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'Content'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);
    $prism->shouldReceive('generate')
        ->withArgs(fn ($p, $m, $sys, $q) => str_contains($sys, 'Answer only in Turkish'))
        ->andReturn('Türkçe cevap.');

    $pipeline = new RagPipeline(
        vectorStore: $store,
        embeddingCache: new EmbeddingCache(enabled: false),
        hybridSearch: new HybridSearch($store),
        reranker: new Reranker(enabled: false),
        prismRetryHandler: $prism,
    );

    $result = $pipeline->systemPrompt('Answer only in Turkish.')->ask('What?');

    expect($result->answer)->toBe('Türkçe cevap.');
});

test('full flow with custom provider and model', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->andReturnSelf();
    $store->shouldReceive('similaritySearch')->andReturn(collect());

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn([0.1]);
    $prism->shouldReceive('generate')
        ->withArgs(fn ($provider, $model) => $provider === 'anthropic' && $model === 'claude-sonnet-4-5')
        ->andReturn('Claude answer.');

    $pipeline = new RagPipeline(
        vectorStore: $store,
        embeddingCache: new EmbeddingCache(enabled: false),
        hybridSearch: new HybridSearch($store),
        reranker: new Reranker(enabled: false),
        prismRetryHandler: $prism,
    );

    $result = $pipeline->using('anthropic', 'claude-sonnet-4-5')->ask('Q?');

    expect($result->answer)->toBe('Claude answer.');
});

test('RagResult toArray serialises correctly', function () {
    $result = new RagResult(
        answer: 'Answer text',
        chunks: collect([['id' => '1', 'score' => 0.9, 'metadata' => ['source' => 'x.md'], 'content' => 'C']]),
        question: 'Q?',
        retrievalTimeMs: 45.5,
        generationTimeMs: 120.3,
    );

    $array = $result->toArray();

    expect($array['answer'])->toBe('Answer text')
        ->and($array['question'])->toBe('Q?')
        ->and($array['sources'])->toHaveCount(1)
        ->and($array['timing']['total_ms'])->toBe(165.8);
});
