<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\CacheIntegrityGuard;

$iterations = (int) (getenv('RAG_ERIS_ITERATIONS') ?: 1000);

test('HMAC hash is deterministic: same input always produces same hash', function () use ($iterations) {
    $key = 'test-app-key-'.random_int(0, 9999);

    for ($i = 0; $i < $iterations; $i++) {
        $text = bin2hex(random_bytes(random_int(1, 500)));

        $hash1 = CacheIntegrityGuard::signedHash($text, $key);
        $hash2 = CacheIntegrityGuard::signedHash($text, $key);

        expect($hash1)->toBe($hash2, "Hash not deterministic for input length ".strlen($text));
    }
});

test('collision resistance: different inputs produce different hashes', function () use ($iterations) {
    $key = 'test-app-key';
    $seen = [];

    for ($i = 0; $i < $iterations; $i++) {
        $text = bin2hex(random_bytes(random_int(1, 500)));
        $hash = CacheIntegrityGuard::signedHash($text, $key);

        // Track if we've seen this hash before with a different input
        if (isset($seen[$hash])) {
            // Verify it's actually the same input (not a collision)
            expect($seen[$hash])->toBe($text, "Hash collision detected!");
        }

        $seen[$hash] = $text;
    }
});

test('HMAC verify succeeds for all valid text/key pairs', function () use ($iterations) {
    for ($i = 0; $i < $iterations; $i++) {
        $text = bin2hex(random_bytes(random_int(1, 200)));
        $key = bin2hex(random_bytes(random_int(8, 64)));

        $hash = CacheIntegrityGuard::signedHash($text, $key);

        expect(CacheIntegrityGuard::verify($hash, $text, $key))->toBeTrue();
    }
});

test('HMAC verify fails for tampered hashes', function () use ($iterations) {
    $key = 'app-key';

    for ($i = 0; $i < min($iterations, 500); $i++) {
        $text = bin2hex(random_bytes(random_int(1, 200)));
        $hash = CacheIntegrityGuard::signedHash($text, $key);

        // Flip one character in the hash
        $tampered = $hash;
        $pos = random_int(0, strlen($tampered) - 1);
        $tampered[$pos] = $tampered[$pos] === 'a' ? 'b' : 'a';

        expect(CacheIntegrityGuard::verify($tampered, $text, $key))->toBeFalse();
    }
});
