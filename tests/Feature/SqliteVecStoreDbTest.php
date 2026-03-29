<?php

declare(strict_types=1);

use Moneo\LaravelRag\Exceptions\VectorStoreException;
use Moneo\LaravelRag\Security\InvalidVectorException;
use Moneo\LaravelRag\VectorStores\SqliteVecStore;

/**
 * Tests for SqliteVecStore.
 * Tests that don't require sqlite-vec extension use :memory: and test validation/helper methods.
 * Tests that require vec0 auto-skip when extension is not available.
 */

test('validates vector dimensions before upsert', function (): void {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3);

    expect(fn () => $store->upsert('id-1', [0.1, 0.2], ['content' => 'test']))
        ->toThrow(InvalidVectorException::class, 'dimension mismatch');
});

test('validates NaN before upsert', function (): void {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3);

    expect(fn () => $store->upsert('id-1', [0.1, NAN, 0.3], ['content' => 'test']))
        ->toThrow(InvalidVectorException::class, 'NaN');
});

test('validates infinity before upsert', function (): void {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3);

    expect(fn () => $store->upsert('id-1', [0.1, INF, 0.3], ['content' => 'test']))
        ->toThrow(InvalidVectorException::class, 'infinite');
});

test('table returns new instance', function (): void {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3);
    $new = $store->table('other_table');

    expect($new)->not->toBe($store)
        ->and($new)->toBeInstanceOf(SqliteVecStore::class);
});

test('table rejects SQL injection', function (): void {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3);

    expect(fn () => $store->table("'; DROP TABLE--"))
        ->toThrow(\InvalidArgumentException::class, 'Invalid table name');
});

test('supportsFullTextSearch returns false', function (): void {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3);

    expect($store->supportsFullTextSearch())->toBeFalse();
});

test('vectorToBlob converts floats correctly', function (): void {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3);
    $ref = new ReflectionClass($store);
    $method = $ref->getMethod('vectorToBlob');
    $method->setAccessible(true);

    $blob = $method->invoke($store, [1.0, 2.0, 3.0]);

    expect(strlen($blob))->toBe(12);
    $unpacked = array_values(unpack('f3', $blob));
    expect($unpacked)->toBe([1.0, 2.0, 3.0]);
});

test('constructor with extension path throws clear error when unavailable', function (): void {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3, extensionPath: '/nonexistent/vec0.so');

    expect(fn () => $store->upsert('id-1', [0.1, 0.2, 0.3], ['content' => 'test']))
        ->toThrow(VectorStoreException::class, 'Failed to initialize');
});
