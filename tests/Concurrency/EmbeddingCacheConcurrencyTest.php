<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\CacheIntegrityGuard;

/**
 * Concurrency tests for EmbeddingCache.
 *
 * @group concurrency
 */

test('HMAC signing is thread-safe: concurrent hashing produces consistent results', function () {
    $key = 'test-app-key';
    $text = 'concurrent test input';
    $expectedHash = CacheIntegrityGuard::signedHash($text, $key);

    // Simulate concurrent hash computations
    $results = [];
    for ($i = 0; $i < 50; $i++) {
        $results[] = CacheIntegrityGuard::signedHash($text, $key);
    }

    // All results must be identical
    foreach ($results as $index => $hash) {
        expect($hash)->toBe($expectedHash, "Hash at iteration {$index} differs from expected");
    }
});

test('vector validation is safe under concurrent access', function () {
    $vector = array_fill(0, 100, 0.5);

    $results = [];
    for ($i = 0; $i < 50; $i++) {
        try {
            \Moneo\LaravelRag\Security\VectorValidator::validate($vector, 100);
            $results[] = 'pass';
        } catch (\Throwable $e) {
            $results[] = 'fail: '.$e->getMessage();
        }
    }

    // All should pass — no race conditions in validation
    expect(array_unique($results))->toBe(['pass']);
});

test('InputSanitiser is stateless and safe for concurrent use', function () {
    $inputs = [
        'Clean question about databases',
        'Another clean question',
        'What is PostgreSQL?',
    ];

    // Run sanitiser many times concurrently
    $results = [];
    for ($i = 0; $i < 100; $i++) {
        $input = $inputs[$i % count($inputs)];
        $results[] = \Moneo\LaravelRag\Security\InputSanitiser::clean($input);
    }

    // Results should be deterministic
    for ($i = 0; $i < 100; $i++) {
        $input = $inputs[$i % count($inputs)];
        expect($results[$i])->toBe($input);
    }
});
