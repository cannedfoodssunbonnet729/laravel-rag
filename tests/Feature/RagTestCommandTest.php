<?php

declare(strict_types=1);

use Moneo\LaravelRag\Facades\Rag;
use Moneo\LaravelRag\Pipeline\RagPipeline;
use Moneo\LaravelRag\Pipeline\RagResult;

test('rag:test dry-run mode retrieves chunks', function () {
    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('from')->andReturn($pipeline);
    $pipeline->shouldReceive('limit')->andReturn($pipeline);
    $pipeline->shouldReceive('hybrid')->andReturn($pipeline);
    $pipeline->shouldReceive('rerank')->andReturn($pipeline);
    $pipeline->shouldReceive('dryRun')->andReturn(collect([
        ['id' => 'c1', 'score' => 0.9, 'metadata' => [], 'content' => 'Test chunk content'],
    ]));

    Rag::shouldReceive('from')->andReturn($pipeline);

    $this->artisan('rag:test', ['question' => 'What is RAG?', '--dry-run' => true])
        ->assertSuccessful();
});

test('rag:test normal mode asks and returns answer', function () {
    $result = new RagResult(
        answer: 'RAG is retrieval augmented generation.',
        chunks: collect([['id' => '1', 'score' => 0.9, 'metadata' => ['source' => 'doc.md'], 'content' => 'ctx']]),
        question: 'What is RAG?',
        retrievalTimeMs: 50.0,
        generationTimeMs: 100.0,
    );

    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('from')->andReturn($pipeline);
    $pipeline->shouldReceive('limit')->andReturn($pipeline);
    $pipeline->shouldReceive('ask')->andReturn($result);

    Rag::shouldReceive('from')->andReturn($pipeline);

    $this->artisan('rag:test', ['question' => 'What is RAG?'])
        ->assertSuccessful();
});

test('rag:test with hybrid and rerank flags', function () {
    $result = new RagResult(
        answer: 'Answer.',
        chunks: collect([]),
        question: 'Q?',
        retrievalTimeMs: 10,
        generationTimeMs: 20,
    );

    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('from')->andReturn($pipeline);
    $pipeline->shouldReceive('limit')->andReturn($pipeline);
    $pipeline->shouldReceive('hybrid')->once()->andReturn($pipeline);
    $pipeline->shouldReceive('rerank')->once()->andReturn($pipeline);
    $pipeline->shouldReceive('ask')->andReturn($result);

    Rag::shouldReceive('from')->andReturn($pipeline);

    $this->artisan('rag:test', ['question' => 'Q?', '--hybrid' => true, '--rerank' => true])
        ->assertSuccessful();
});
