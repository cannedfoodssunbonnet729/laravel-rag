<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests\Contract;

use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;
use Moneo\LaravelRag\VectorStores\SqliteVecStore;

/**
 * Contract tests for SqliteVecStore.
 *
 * These tests verify that SqliteVecStore satisfies the VectorStoreContract.
 * Note: These tests require sqlite-vec extension to be loaded.
 *
 * @group sqlite-vec
 * @group contract
 */
class SqliteVecStoreContractTest extends VectorStoreContractTest
{
    protected function createStore(): VectorStoreContract
    {
        return new SqliteVecStore(
            connection: 'sqlite',
            dimensions: (int) config('rag.embedding.dimensions', 3),
        );
    }

    protected function setUpStoreSchema(): void
    {
        // sqlite-vec requires the extension to be loaded
        // These tests are skipped in CI unless sqlite-vec is available
        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite3 extension not available');
        }
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('rag.embedding.dimensions', 3);
    }
}
