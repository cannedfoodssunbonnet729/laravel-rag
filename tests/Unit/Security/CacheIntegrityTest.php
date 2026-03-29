<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\CacheIntegrityException;
use Moneo\LaravelRag\Security\CacheIntegrityGuard;

test('signedHash produces consistent hashes', function () {
    $hash1 = CacheIntegrityGuard::signedHash('test', 'key');
    $hash2 = CacheIntegrityGuard::signedHash('test', 'key');

    expect($hash1)->toBe($hash2);
});

test('signedHash produces different hashes for different keys', function () {
    $hash1 = CacheIntegrityGuard::signedHash('test', 'key1');
    $hash2 = CacheIntegrityGuard::signedHash('test', 'key2');

    expect($hash1)->not->toBe($hash2);
});

test('signedHash produces different hashes for different text', function () {
    $hash1 = CacheIntegrityGuard::signedHash('text1', 'key');
    $hash2 = CacheIntegrityGuard::signedHash('text2', 'key');

    expect($hash1)->not->toBe($hash2);
});

test('verify returns true for valid hash', function () {
    $hash = CacheIntegrityGuard::signedHash('test', 'key');

    expect(CacheIntegrityGuard::verify($hash, 'test', 'key'))->toBeTrue();
});

test('verify returns false for tampered hash', function () {
    expect(CacheIntegrityGuard::verify('tampered', 'test', 'key'))->toBeFalse();
});

test('verify returns false for wrong text', function () {
    $hash = CacheIntegrityGuard::signedHash('text1', 'key');

    expect(CacheIntegrityGuard::verify($hash, 'text2', 'key'))->toBeFalse();
});

test('validateCachedVector accepts valid float array', function () {
    $result = CacheIntegrityGuard::validateCachedVector([0.1, 0.2, 0.3]);

    expect($result)->toBe([0.1, 0.2, 0.3]);
});

test('validateCachedVector converts integers to floats', function () {
    $result = CacheIntegrityGuard::validateCachedVector([1, 2, 3]);

    expect($result)->toBe([1.0, 2.0, 3.0]);
});

test('validateCachedVector rejects non-array', function () {
    CacheIntegrityGuard::validateCachedVector('not an array');
})->throws(CacheIntegrityException::class, 'not an array');

test('validateCachedVector rejects empty array', function () {
    CacheIntegrityGuard::validateCachedVector([]);
})->throws(CacheIntegrityException::class, 'empty');

test('validateCachedVector rejects array with string', function () {
    CacheIntegrityGuard::validateCachedVector([0.1, 'bad', 0.3]);
})->throws(CacheIntegrityException::class, 'not a number');

test('validateCachedVector rejects null', function () {
    CacheIntegrityGuard::validateCachedVector(null);
})->throws(CacheIntegrityException::class, 'not an array');
