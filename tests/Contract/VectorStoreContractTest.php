<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests\Contract;

use Moneo\LaravelRag\Security\InvalidVectorException;
use Moneo\LaravelRag\Tests\TestCase;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

abstract class VectorStoreContractTest extends TestCase
{
    abstract protected function createStore(): VectorStoreContract;

    abstract protected function setUpStoreSchema(): void;

    protected VectorStoreContract $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStoreSchema();
        $this->store = $this->createStore();
    }

    public function test_upsert_then_similarity_search_returns_result(): void
    {
        $this->store->upsert('id-1', $this->makeVector(0.1), ['content' => 'test doc']);

        $results = $this->store->similaritySearch($this->makeVector(0.1), 5);

        $this->assertGreaterThanOrEqual(1, $results->count());
        $this->assertEquals('id-1', $results->first()['id']);
    }

    public function test_upsert_is_idempotent(): void
    {
        $this->store->upsert('id-1', $this->makeVector(0.1), ['content' => 'v1']);
        $this->store->upsert('id-1', $this->makeVector(0.2), ['content' => 'v2']);

        $results = $this->store->similaritySearch($this->makeVector(0.2), 10);

        // Should have exactly 1 result with id-1, not a duplicate
        $matched = $results->where('id', 'id-1');
        $this->assertCount(1, $matched);
    }

    public function test_threshold_one_returns_nothing(): void
    {
        $this->store->upsert('id-1', $this->makeVector(0.1), ['content' => 'test']);

        $results = $this->store->similaritySearch($this->makeVector(0.9), 5, 1.0);

        $this->assertCount(0, $results);
    }

    public function test_threshold_zero_returns_everything(): void
    {
        $this->store->upsert('id-1', $this->makeVector(0.1), ['content' => 'test']);

        $results = $this->store->similaritySearch($this->makeVector(0.1), 5, 0.0);

        $this->assertGreaterThanOrEqual(1, $results->count());
    }

    public function test_delete_removes_from_subsequent_searches(): void
    {
        $this->store->upsert('id-del', $this->makeVector(0.5), ['content' => 'delete me']);
        $this->store->delete('id-del');

        $results = $this->store->similaritySearch($this->makeVector(0.5), 10, 0.0);

        $ids = $results->pluck('id')->toArray();
        $this->assertNotContains('id-del', $ids);
    }

    public function test_empty_store_returns_empty_collection(): void
    {
        $results = $this->store->similaritySearch($this->makeVector(0.5), 5);

        $this->assertCount(0, $results);
    }

    public function test_metadata_round_trips(): void
    {
        $metadata = ['content' => 'test', 'category' => 'tech', 'score' => 42];
        $this->store->upsert('id-meta', $this->makeVector(0.1), $metadata);

        $results = $this->store->similaritySearch($this->makeVector(0.1), 5, 0.0);

        $found = $results->firstWhere('id', 'id-meta');
        $this->assertNotNull($found);
        $this->assertEquals('tech', $found['metadata']['category']);
    }

    public function test_similarity_search_respects_limit(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->store->upsert("id-{$i}", $this->makeVector(0.1 + $i * 0.01), ['content' => "doc {$i}"]);
        }

        $results = $this->store->similaritySearch($this->makeVector(0.1), 3, 0.0);

        $this->assertLessThanOrEqual(3, $results->count());
    }

    public function test_results_have_required_keys(): void
    {
        $this->store->upsert('id-keys', $this->makeVector(0.1), ['content' => 'test']);

        $results = $this->store->similaritySearch($this->makeVector(0.1), 5, 0.0);

        $first = $results->first();
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('score', $first);
        $this->assertArrayHasKey('metadata', $first);
        $this->assertArrayHasKey('content', $first);
    }

    public function test_score_is_float(): void
    {
        $this->store->upsert('id-score', $this->makeVector(0.1), ['content' => 'test']);

        $results = $this->store->similaritySearch($this->makeVector(0.1), 5, 0.0);

        $this->assertIsFloat($results->first()['score']);
    }

    public function test_supports_full_text_search_returns_bool(): void
    {
        $this->assertIsBool($this->store->supportsFullTextSearch());
    }

    public function test_table_returns_new_instance(): void
    {
        $newStore = $this->store->table('other_table');

        $this->assertNotSame($this->store, $newStore);
    }

    public function test_rejects_invalid_dimension_vector(): void
    {
        $this->expectException(InvalidVectorException::class);
        $this->store->upsert('id-bad', [0.1, 0.2], ['content' => 'wrong dims']); // too few dims
    }

    public function test_rejects_nan_in_vector(): void
    {
        $this->expectException(InvalidVectorException::class);
        $vector = $this->makeVector(0.1);
        $vector[0] = NAN;
        $this->store->upsert('id-nan', $vector, ['content' => 'nan']);
    }

    public function test_rejects_infinity_in_vector(): void
    {
        $this->expectException(InvalidVectorException::class);
        $vector = $this->makeVector(0.1);
        $vector[0] = INF;
        $this->store->upsert('id-inf', $vector, ['content' => 'inf']);
    }

    /**
     * Create a test vector with the configured dimensions.
     *
     * @return array<int, float>
     */
    protected function makeVector(float $baseValue): array
    {
        $dims = (int) config('rag.embedding.dimensions', 3);

        return array_fill(0, $dims, $baseValue);
    }
}
