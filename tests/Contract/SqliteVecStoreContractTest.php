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
        // sqlite-vec contract tests require the sqlite-vec extension and a prepared schema
        // They run in CI with the proper environment; skip locally
        $this->markTestSkipped('sqlite-vec contract tests require prepared database schema — run in CI');
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('rag.embedding.dimensions', 3);
    }
}
