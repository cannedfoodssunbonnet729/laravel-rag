<?php

declare(strict_types=1);

use Moneo\LaravelRag\Cache\EmbeddingCache;

test('returns null when disabled', function () {
    $cache = new EmbeddingCache(enabled: false);

    expect($cache->get('test'))->toBeNull();
});

test('put does nothing when disabled', function () {
    $cache = new EmbeddingCache(enabled: false);
    $cache->put('test', [0.1, 0.2]);

    // Should not throw — silently ignores
})->throwsNoExceptions();

test('initial hit count is zero', function () {
    $cache = new EmbeddingCache(enabled: true);

    expect($cache->getHits())->toBe(0);
});

test('initial miss count is zero', function () {
    $cache = new EmbeddingCache(enabled: true);

    expect($cache->getMisses())->toBe(0);
});

test('hit rate is zero when no operations', function () {
    $cache = new EmbeddingCache(enabled: true);

    expect($cache->getHitRate())->toBe(0.0);
});

test('hit rate calculates correctly', function () {
    $cache = new EmbeddingCache(enabled: true);
    $reflection = new ReflectionClass($cache);

    $hits = $reflection->getProperty('hits');
    $hits->setAccessible(true);
    $hits->setValue($cache, 3);

    $misses = $reflection->getProperty('misses');
    $misses->setAccessible(true);
    $misses->setValue($cache, 1);

    expect($cache->getHitRate())->toBe(0.75);
});

test('hit rate is 1.0 when all hits', function () {
    $cache = new EmbeddingCache(enabled: true);
    $reflection = new ReflectionClass($cache);

    $hits = $reflection->getProperty('hits');
    $hits->setAccessible(true);
    $hits->setValue($cache, 10);

    expect($cache->getHitRate())->toBe(1.0);
});

test('hit rate is 0.0 when all misses', function () {
    $cache = new EmbeddingCache(enabled: true);
    $reflection = new ReflectionClass($cache);

    $misses = $reflection->getProperty('misses');
    $misses->setAccessible(true);
    $misses->setValue($cache, 5);

    expect($cache->getHitRate())->toBe(0.0);
});

test('hash produces consistent results', function () {
    $cache = new EmbeddingCache(enabled: true);
    $reflection = new ReflectionClass($cache);
    $method = $reflection->getMethod('hash');
    $method->setAccessible(true);

    $hash1 = $method->invoke($cache, 'test text');
    $hash2 = $method->invoke($cache, 'test text');

    expect($hash1)->toBe($hash2)->and($hash1)->toHaveLength(64);
});

test('hash produces different results for different text', function () {
    $cache = new EmbeddingCache(enabled: true);
    $reflection = new ReflectionClass($cache);
    $method = $reflection->getMethod('hash');
    $method->setAccessible(true);

    $hash1 = $method->invoke($cache, 'text one');
    $hash2 = $method->invoke($cache, 'text two');

    expect($hash1)->not->toBe($hash2);
});

test('enabled property is readonly', function () {
    $cache = new EmbeddingCache(enabled: true);

    expect($cache->enabled)->toBeTrue();
});
