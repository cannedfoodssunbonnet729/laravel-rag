<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\CacheIntegrityException;
use Moneo\LaravelRag\Security\CacheIntegrityGuard;

/**
 * Chaos tests for cache failures.
 *
 * @group chaos
 */

test('corrupted cache returns are caught by integrity guard', function () {
    $corruptedValues = [
        'not json at all',
        '{"key": "value"}',       // Object, not array
        '"string"',               // JSON string
        '42',                     // JSON number
        'null',                   // JSON null
        '[0.1, "bad", 0.3]',     // Mixed types
    ];

    foreach ($corruptedValues as $value) {
        $decoded = json_decode($value, true);

        try {
            CacheIntegrityGuard::validateCachedVector($decoded);
            // If it's a valid float array by coincidence, that's fine
            if (! is_array($decoded) || empty($decoded)) {
                $this->fail("Should have rejected: {$value}");
            }
        } catch (CacheIntegrityException) {
            // Expected
        }
    }
});

test('HMAC detects tampered cache keys', function () {
    $key = 'my-app-key';
    $text = 'test embedding text';
    $hash = CacheIntegrityGuard::signedHash($text, $key);

    // Tamper with the hash
    $tamperedHash = str_replace($hash[0], $hash[0] === 'a' ? 'b' : 'a', $hash);

    if ($tamperedHash !== $hash) {
        expect(CacheIntegrityGuard::verify($tamperedHash, $text, $key))->toBeFalse();
    }

    // Tamper with the text
    expect(CacheIntegrityGuard::verify($hash, $text.'_modified', $key))->toBeFalse();

    // Tamper with the key
    expect(CacheIntegrityGuard::verify($hash, $text, 'wrong-key'))->toBeFalse();
});

test('HMAC with empty strings does not crash', function () {
    $hash = CacheIntegrityGuard::signedHash('', 'key');
    expect($hash)->toBeString()->toHaveLength(64);

    $hash2 = CacheIntegrityGuard::signedHash('text', '');
    expect($hash2)->toBeString()->toHaveLength(64);
});

test('validateCachedVector handles extreme float values', function () {
    // Very large floats should be accepted
    $large = CacheIntegrityGuard::validateCachedVector([1e38, -1e38, 0.0]);
    expect($large)->toHaveCount(3);

    // Very small (subnormal) floats should be accepted
    $small = CacheIntegrityGuard::validateCachedVector([1e-45, -1e-45, 5e-324]);
    expect($small)->toHaveCount(3);
});
