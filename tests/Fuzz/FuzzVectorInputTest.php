<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\InvalidVectorException;
use Moneo\LaravelRag\Security\VectorValidator;

/**
 * Fuzz tests for VectorValidator — random vectors.
 *
 * @group fuzz
 */

test('catches all invalid vectors without crashing', function () {
    for ($i = 0; $i < 1000; $i++) {
        $dims = random_int(0, 100);
        $expectedDims = random_int(0, 100);

        // Generate a vector with random types mixed in
        $vector = [];
        for ($j = 0; $j < $dims; $j++) {
            $vector[] = match (random_int(0, 6)) {
                0 => (float) random_int(-1000, 1000) / 100,
                1 => random_int(-100, 100),
                2 => NAN,
                3 => INF,
                4 => -INF,
                5 => 'string',
                6 => null,
            };
        }

        try {
            VectorValidator::validate($vector, $expectedDims);
            // If it passes, verify it's actually valid
            expect(count($vector))->toBe($expectedDims);
            foreach ($vector as $v) {
                expect(is_float($v) || is_int($v))->toBeTrue();
                if (is_float($v)) {
                    expect(is_nan($v))->toBeFalse();
                    expect(is_infinite($v))->toBeFalse();
                }
            }
        } catch (InvalidVectorException) {
            // Expected for invalid vectors
        }
    }
});

test('handles extreme dimension counts', function () {
    $largeDims = 10000;
    $vector = array_fill(0, $largeDims, 0.1);

    VectorValidator::validate($vector, $largeDims);
    expect(true)->toBeTrue();
});

test('validates in reasonable time for large vectors', function () {
    $vector = array_fill(0, 3072, 0.1);

    $start = microtime(true);
    for ($i = 0; $i < 100; $i++) {
        VectorValidator::validate($vector, 3072);
    }
    $elapsed = (microtime(true) - $start) * 1000;

    expect($elapsed)->toBeLessThan(1000); // 100 validations under 1 second
});
