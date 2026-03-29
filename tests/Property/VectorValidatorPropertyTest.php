<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\InvalidVectorException;
use Moneo\LaravelRag\Security\VectorValidator;

$iterations = (int) (getenv('RAG_ERIS_ITERATIONS') ?: 1000);

test('accepts all valid float vectors of correct dimension', function () use ($iterations) {
    for ($i = 0; $i < $iterations; $i++) {
        $dims = random_int(1, 3072);
        $vector = [];
        for ($j = 0; $j < $dims; $j++) {
            $vector[] = (random_int(-100000, 100000)) / 100000.0;
        }

        VectorValidator::validate($vector, $dims);
    }

    // If we get here without exception, all passed
    expect(true)->toBeTrue();
});

test('rejects all vectors with wrong dimension', function () use ($iterations) {
    for ($i = 0; $i < min($iterations, 500); $i++) {
        $actualDims = random_int(1, 100);
        $expectedDims = $actualDims + random_int(1, 50); // always different
        $vector = array_fill(0, $actualDims, 0.1);

        try {
            VectorValidator::validate($vector, $expectedDims);
            $this->fail("Should have thrown for dims={$actualDims} expected={$expectedDims}");
        } catch (InvalidVectorException) {
            // Expected
        }
    }

    expect(true)->toBeTrue();
});

test('rejects any vector containing NaN at any position', function () use ($iterations) {
    for ($i = 0; $i < min($iterations, 500); $i++) {
        $dims = random_int(1, 100);
        $vector = array_fill(0, $dims, 0.5);
        $nanPos = random_int(0, $dims - 1);
        $vector[$nanPos] = NAN;

        try {
            VectorValidator::validate($vector, $dims);
            $this->fail("Should have thrown for NaN at position {$nanPos}");
        } catch (InvalidVectorException) {
            // Expected
        }
    }

    expect(true)->toBeTrue();
});

test('accepts zero vectors of any dimension', function () {
    for ($dims = 1; $dims <= 100; $dims++) {
        $vector = array_fill(0, $dims, 0.0);
        VectorValidator::validate($vector, $dims);
    }

    expect(true)->toBeTrue();
});
