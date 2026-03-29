<?php

declare(strict_types=1);

use Moneo\LaravelRag\Search\HybridSearch;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

$iterations = (int) (getenv('RAG_ERIS_ITERATIONS') ?: 1000);

function makeHybridSearch(): HybridSearch
{
    $store = Mockery::mock(VectorStoreContract::class);

    return new HybridSearch($store, rrfK: 60);
}

function randomResultList(int $maxSize): \Illuminate\Support\Collection
{
    $size = random_int(0, $maxSize);
    $items = [];
    for ($i = 0; $i < $size; $i++) {
        $items[] = [
            'id' => 'item-'.random_int(0, 200),
            'score' => random_int(0, 1000) / 1000,
            'metadata' => [],
            'content' => 'content-'.$i,
        ];
    }

    return collect($items);
}

test('merged count <= |list_a| + |list_b| for all input lists', function () use ($iterations) {
    $search = makeHybridSearch();

    for ($i = 0; $i < $iterations; $i++) {
        $a = randomResultList(50);
        $b = randomResultList(50);
        $limit = random_int(1, 100);

        $merged = $search->mergeWithRRF($a, $b, 0.7, 0.3, $limit);

        $uniqueIds = $a->pluck('id')->merge($b->pluck('id'))->unique()->count();
        expect($merged->count())->toBeLessThanOrEqual(min($limit, $uniqueIds));
    }
});

test('top result has highest RRF score', function () use ($iterations) {
    $search = makeHybridSearch();

    for ($i = 0; $i < $iterations; $i++) {
        $a = randomResultList(20);
        $b = randomResultList(20);

        if ($a->isEmpty() && $b->isEmpty()) {
            continue;
        }

        $merged = $search->mergeWithRRF($a, $b, 0.7, 0.3, 50);

        if ($merged->count() <= 1) {
            continue;
        }

        $scores = $merged->pluck('score')->toArray();
        for ($j = 1; $j < count($scores); $j++) {
            expect($scores[$j])->toBeLessThanOrEqual($scores[0] + 0.0001,
                "Result at position {$j} has score {$scores[$j]} > top score {$scores[0]}"
            );
        }
    }
});

test('RRF is commutative: order of input lists does not matter', function () use ($iterations) {
    $search = makeHybridSearch();

    for ($i = 0; $i < min($iterations, 500); $i++) {
        $a = randomResultList(10);
        $b = randomResultList(10);

        $merged_ab = $search->mergeWithRRF($a, $b, 0.5, 0.5, 50);
        $merged_ba = $search->mergeWithRRF($b, $a, 0.5, 0.5, 50);

        // Same IDs in same order when weights are equal
        expect($merged_ab->pluck('id')->toArray())
            ->toBe($merged_ba->pluck('id')->toArray());
    }
});
