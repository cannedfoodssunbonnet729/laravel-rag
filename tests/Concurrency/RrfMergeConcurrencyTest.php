<?php

declare(strict_types=1);

use Moneo\LaravelRag\Search\HybridSearch;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

/**
 * Concurrency tests for RRF merge.
 *
 * @group concurrency
 */

test('RRF merge produces deterministic results under concurrent access', function () {
    $store = Mockery::mock(VectorStoreContract::class);
    $search = new HybridSearch($store, rrfK: 60);

    $semantic = collect([
        ['id' => 'a', 'score' => 0.9, 'metadata' => [], 'content' => 'A'],
        ['id' => 'b', 'score' => 0.8, 'metadata' => [], 'content' => 'B'],
    ]);

    $fulltext = collect([
        ['id' => 'b', 'score' => 0.95, 'metadata' => [], 'content' => 'B'],
        ['id' => 'c', 'score' => 0.85, 'metadata' => [], 'content' => 'C'],
    ]);

    // Run merge 50 times "concurrently" (sequential in PHP, but verifies no shared state mutation)
    $firstResult = null;
    for ($i = 0; $i < 50; $i++) {
        $result = $search->mergeWithRRF($semantic, $fulltext, 0.7, 0.3, 5);

        if ($firstResult === null) {
            $firstResult = $result->pluck('id')->toArray();
        } else {
            expect($result->pluck('id')->toArray())->toBe($firstResult,
                "Iteration {$i} produced different order than iteration 0"
            );
        }
    }
});
