<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\InvalidVectorException;
use Moneo\LaravelRag\VectorStores\PgvectorStore;
use Moneo\LaravelRag\VectorStores\SqliteVecStore;

/**
 * Chaos tests for vector store failures.
 *
 * @group chaos
 */

test('PgvectorStore rejects SQL injection in table name', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 3);

    $maliciousNames = [
        "'; DROP TABLE users; --",
        'Robert; DROP TABLE students--',
        "table\x00name",
        'table name with spaces',
        '../../../etc/passwd',
        'SELECT * FROM',
        "table'name",
        'table"name',
    ];

    foreach ($maliciousNames as $name) {
        try {
            $store->table($name);
            $this->fail("Should have rejected table name: {$name}");
        } catch (\InvalidArgumentException) {
            // Expected
        }
    }
});

test('SqliteVecStore rejects SQL injection in table name', function () {
    $store = new SqliteVecStore(database: ':memory:', dimensions: 3);

    $maliciousNames = [
        "'; DROP TABLE users; --",
        'Robert; DROP TABLE students--',
        'table name',
        "table'name",
    ];

    foreach ($maliciousNames as $name) {
        try {
            $store->table($name);
            $this->fail("Should have rejected table name: {$name}");
        } catch (\InvalidArgumentException) {
            // Expected
        }
    }
});

test('VectorValidator catches all malformed vectors before they reach the store', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 3);

    $malformedVectors = [
        [0.1, NAN, 0.3],         // NaN
        [0.1, INF, 0.3],         // Infinity
        [0.1, -INF, 0.3],        // Negative infinity
        [0.1, 0.2],              // Wrong dimensions
        [0.1, 0.2, 0.3, 0.4],   // Too many dimensions
    ];

    foreach ($malformedVectors as $vector) {
        try {
            $store->upsert('test', $vector, ['content' => 'test']);
            $this->fail('Should have rejected malformed vector: '.json_encode($vector));
        } catch (InvalidVectorException) {
            // Expected — caught before reaching the database
        }
    }
});

test('PgvectorStore accepts valid table names', function () {
    $store = new PgvectorStore(connection: 'pgsql', dimensions: 3);

    $validNames = [
        'documents',
        'my_documents',
        'Documents2',
        'public.documents',
        '_private_table',
    ];

    foreach ($validNames as $name) {
        $newStore = $store->table($name);
        expect($newStore)->toBeInstanceOf(PgvectorStore::class);
    }
});
