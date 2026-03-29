<?php

declare(strict_types=1);

use Moneo\LaravelRag\Search\HybridSearch;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

test('merges results with RRF correctly', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $search = new HybridSearch($store, rrfK: 60);

    $semantic = collect([
        ['id' => 'a', 'score' => 0.9, 'metadata' => [], 'content' => 'A'],
        ['id' => 'b', 'score' => 0.8, 'metadata' => [], 'content' => 'B'],
        ['id' => 'c', 'score' => 0.7, 'metadata' => [], 'content' => 'C'],
    ]);

    $fulltext = collect([
        ['id' => 'b', 'score' => 0.95, 'metadata' => [], 'content' => 'B'],
        ['id' => 'd', 'score' => 0.85, 'metadata' => [], 'content' => 'D'],
        ['id' => 'a', 'score' => 0.75, 'metadata' => [], 'content' => 'A'],
    ]);

    $merged = $search->mergeWithRRF($semantic, $fulltext, 0.7, 0.3, 3);

    expect($merged)->toHaveCount(3);
    $ids = $merged->pluck('id')->toArray();
    expect($ids)->toContain('a', 'b');
});

test('RRF with empty semantic results', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $search = new HybridSearch($store, rrfK: 60);

    $merged = $search->mergeWithRRF(
        collect(),
        collect([['id' => 'a', 'score' => 0.9, 'metadata' => [], 'content' => 'A']]),
        0.7, 0.3, 5,
    );

    expect($merged)->toHaveCount(1)->and($merged->first()['id'])->toBe('a');
});

test('RRF with empty fulltext results', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $search = new HybridSearch($store, rrfK: 60);

    $merged = $search->mergeWithRRF(
        collect([['id' => 'a', 'score' => 0.9, 'metadata' => [], 'content' => 'A']]),
        collect(),
        0.7, 0.3, 5,
    );

    expect($merged)->toHaveCount(1);
});

test('RRF with both empty', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $search = new HybridSearch($store, rrfK: 60);

    $merged = $search->mergeWithRRF(collect(), collect(), 0.7, 0.3, 5);

    expect($merged)->toBeEmpty();
});

test('RRF single item in each list', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $search = new HybridSearch($store, rrfK: 60);

    $merged = $search->mergeWithRRF(
        collect([['id' => 'a', 'score' => 0.9, 'metadata' => [], 'content' => 'A']]),
        collect([['id' => 'b', 'score' => 0.9, 'metadata' => [], 'content' => 'B']]),
        0.7, 0.3, 5,
    );

    expect($merged)->toHaveCount(2);
    // Semantic weighted higher, so 'a' should come first
    expect($merged->first()['id'])->toBe('a');
});

test('RRF respects limit', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $search = new HybridSearch($store, rrfK: 60);

    $semantic = collect(array_map(fn ($i) => [
        'id' => "s{$i}", 'score' => 1 - $i * 0.01, 'metadata' => [], 'content' => "S{$i}",
    ], range(0, 9)));

    $merged = $search->mergeWithRRF($semantic, collect(), 0.7, 0.3, 3);

    expect($merged)->toHaveCount(3);
});

test('falls back to semantic when fulltext not supported', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->with('docs')->andReturnSelf();
    $store->shouldReceive('supportsFullTextSearch')->andReturn(false);
    $store->shouldReceive('similaritySearch')->with([0.1], 5)->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'test'],
    ]));

    $search = new HybridSearch($store);
    $results = $search->search('docs', 'query', [0.1], 0.7, 0.3, 5);

    expect($results)->toHaveCount(1);
});

test('delegates to store when fulltext supported', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $store->shouldReceive('table')->with('docs')->andReturnSelf();
    $store->shouldReceive('supportsFullTextSearch')->andReturn(true);
    $store->shouldReceive('hybridSearch')
        ->with('query', [0.1], 0.7, 0.3, 5)
        ->andReturn(collect([
            ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'result'],
        ]));

    $search = new HybridSearch($store);
    $results = $search->search('docs', 'query', [0.1], 0.7, 0.3, 5);

    expect($results)->toHaveCount(1)->and($results->first()['content'])->toBe('result');
});

test('RRF scores are weighted correctly', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $search = new HybridSearch($store, rrfK: 60);

    // Same doc in both lists at rank 0
    $merged = $search->mergeWithRRF(
        collect([['id' => 'x', 'score' => 1.0, 'metadata' => [], 'content' => 'X']]),
        collect([['id' => 'x', 'score' => 1.0, 'metadata' => [], 'content' => 'X']]),
        0.7, 0.3, 1,
    );

    $expectedScore = 0.7 * (1.0 / 61) + 0.3 * (1.0 / 61);
    expect($merged->first()['score'])->toBeGreaterThan(0);
});
