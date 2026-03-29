<?php

declare(strict_types=1);

use Moneo\LaravelRag\VectorStores\SqliteVecStore;

test('vectorToBlob produces correct byte length', function () {
    $store = new SqliteVecStore(connection: 'sqlite', dimensions: 3);
    $reflection = new ReflectionClass($store);
    $method = $reflection->getMethod('vectorToBlob');
    $method->setAccessible(true);

    $result = $method->invoke($store, [1.0, 2.0, 3.0]);

    expect(strlen($result))->toBe(12); // 3 floats × 4 bytes
});

test('vectorToBlob empty array produces empty string', function () {
    $store = new SqliteVecStore(connection: 'sqlite', dimensions: 0);
    $reflection = new ReflectionClass($store);
    $method = $reflection->getMethod('vectorToBlob');
    $method->setAccessible(true);

    $result = $method->invoke($store, []);

    expect(strlen($result))->toBe(0);
});

test('vectorToBlob high dimensions (3072)', function () {
    $store = new SqliteVecStore(connection: 'sqlite', dimensions: 3072);
    $reflection = new ReflectionClass($store);
    $method = $reflection->getMethod('vectorToBlob');
    $method->setAccessible(true);

    $vector = array_fill(0, 3072, 0.1);
    $result = $method->invoke($store, $vector);

    expect(strlen($result))->toBe(3072 * 4);
});

test('does not support full text search', function () {
    $store = new SqliteVecStore(connection: 'sqlite', dimensions: 1536);

    expect($store->supportsFullTextSearch())->toBeFalse();
});

test('table returns new instance', function () {
    $store = new SqliteVecStore(connection: 'sqlite', dimensions: 1536);
    $newStore = $store->table('custom_table');

    expect($newStore)->not->toBe($store);
});

test('table rejects SQL injection', function () {
    $store = new SqliteVecStore(connection: 'sqlite', dimensions: 1536);
    $store->table('Robert; DROP TABLE students--');
})->throws(\InvalidArgumentException::class, 'Invalid table name');

test('table accepts valid names', function () {
    $store = new SqliteVecStore(connection: 'sqlite', dimensions: 1536);
    $newStore = $store->table('documents_v2');

    expect($newStore)->toBeInstanceOf(SqliteVecStore::class);
});
