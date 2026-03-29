<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests\Contract;

use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;
use Moneo\LaravelRag\VectorStores\SqliteVecStore;

/**
 * @group sqlite-vec
 * @group contract
 */
class SqliteVecStoreContractTest extends VectorStoreContractTest
{
    private static bool $vecAvailable = false;

    private static string $dbPath = '/tmp/laravel_rag_vec_test.sqlite';

    protected function createStore(): VectorStoreContract
    {
        return new SqliteVecStore(
            database: self::$dbPath,
            dimensions: (int) config('rag.embedding.dimensions', 3),
        );
    }

    protected function setUpStoreSchema(): void
    {
        if (! self::$vecAvailable) {
            $this->markTestSkipped('sqlite-vec not loadable — set sqlite3.extension_dir in php.ini');
        }

        // Create fresh DB with vec0 tables
        @unlink(self::$dbPath);
        $db = new \SQLite3(self::$dbPath);
        $db->enableExceptions(true);
        $db->loadExtension('vec0.so');
        $db->exec('CREATE TABLE documents (id TEXT PRIMARY KEY, embedding BLOB, metadata TEXT, content TEXT, created_at TEXT, updated_at TEXT)');
        $db->exec('CREATE VIRTUAL TABLE documents_vec USING vec0(embedding float[3])');
        $db->close();
    }

    protected function defineEnvironment($app): void
    {
        // Check vec availability once
        if (! self::$vecAvailable) {
            try {
                $db = new \SQLite3(':memory:');
                $db->enableExceptions(true);
                $db->loadExtension('vec0.so');
                $db->close();
                self::$vecAvailable = true;
            } catch (\Throwable) {
                self::$vecAvailable = false;
            }
        }

        parent::defineEnvironment($app);
        $app['config']->set('rag.embedding.dimensions', 3);
        $app['config']->set('rag.stores.sqlite-vec.database', self::$dbPath);
    }

    protected function defineDatabaseMigrations(): void
    {
        // Skip parent's migrations — we manage schema in setUpStoreSchema
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::$dbPath);
    }
}
