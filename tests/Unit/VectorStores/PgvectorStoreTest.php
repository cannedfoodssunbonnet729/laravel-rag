<?php

declare(strict_types=1);

use Moneo\LaravelRag\VectorStores\PgvectorStore;

test('vectorToString formats correctly', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 3);
    $reflection = new ReflectionClass($store);
    $method = $reflection->getMethod('vectorToString');
    $method->setAccessible(true);

    expect($method->invoke($store, [0.1, 0.2, 0.3]))->toBe('[0.1,0.2,0.3]');
});

test('vectorToString handles empty array', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 0);
    $reflection = new ReflectionClass($store);
    $method = $reflection->getMethod('vectorToString');
    $method->setAccessible(true);

    expect($method->invoke($store, []))->toBe('[]');
});

test('toTsQuery converts words to AND query', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 1536);
    $reflection = new ReflectionClass($store);
    $method = $reflection->getMethod('toTsQuery');
    $method->setAccessible(true);

    expect($method->invoke($store, 'hello world test'))->toBe('hello & world & test');
});

test('toTsQuery handles single word', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 1536);
    $reflection = new ReflectionClass($store);
    $method = $reflection->getMethod('toTsQuery');
    $method->setAccessible(true);

    expect($method->invoke($store, 'hello'))->toBe('hello');
});

test('supportsFullTextSearch returns true', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 1536);

    expect($store->supportsFullTextSearch())->toBeTrue();
});

test('table returns new instance', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 1536);
    $newStore = $store->table('custom_table');

    expect($newStore)->not->toBe($store);
});

test('table rejects SQL injection patterns', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 1536);
    $store->table('Robert; DROP TABLE students--');
})->throws(\InvalidArgumentException::class, 'Invalid table name');

test('table rejects spaces', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 1536);
    $store->table('my table');
})->throws(\InvalidArgumentException::class, 'Invalid table name');

test('table accepts valid names with underscores', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 1536);
    $newStore = $store->table('my_documents');

    expect($newStore)->toBeInstanceOf(PgvectorStore::class);
});

test('table accepts schema-qualified names', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 1536);
    $newStore = $store->table('public.documents');

    expect($newStore)->toBeInstanceOf(PgvectorStore::class);
});
