<?php

declare(strict_types=1);

use Moneo\LaravelRag\Agentic\AgenticResult;
use Moneo\LaravelRag\Agentic\AgenticRetriever;
use Moneo\LaravelRag\Pipeline\RagPipeline;
use Moneo\LaravelRag\Support\PrismRetryHandler;

test('ask returns AgenticResult', function () {
    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('retrieve')
        ->andReturn(collect([
            ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'Relevant chunk'],
        ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    // Sufficiency evaluation returns sufficient=true
    $prism->shouldReceive('generate')
        ->andReturn('{"sufficient": true, "refined_query": null}', 'Final answer based on context.');

    app()->instance(PrismRetryHandler::class, $prism);

    $retriever = new AgenticRetriever(pipeline: $pipeline, maxSteps: 3);
    $result = $retriever->ask('What is X?');

    expect($result)->toBeInstanceOf(AgenticResult::class)
        ->and($result->answer)->toBe('Final answer based on context.')
        ->and($result->stepCount())->toBe(1)
        ->and($result->totalChunksRetrieved)->toBe(1);
});

test('ask performs multiple retrieval steps when insufficient', function () {
    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('retrieve')
        ->andReturn(collect([
            ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'Chunk'],
        ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('generate')
        ->andReturn(
            '{"sufficient": false, "refined_query": "more specific query"}', // Step 1: insufficient
            '{"sufficient": true, "refined_query": null}',                    // Step 2: sufficient
            'The final answer.',                                               // Generate answer
        );

    app()->instance(PrismRetryHandler::class, $prism);

    $retriever = new AgenticRetriever(pipeline: $pipeline, maxSteps: 5);
    $result = $retriever->ask('Vague question?');

    expect($result->stepCount())->toBe(2);
});

test('ask stops at maxSteps even if never sufficient', function () {
    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('retrieve')->andReturn(collect([
        ['id' => '1', 'score' => 0.5, 'metadata' => [], 'content' => 'Partial'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    // Always returns insufficient
    $prism->shouldReceive('generate')
        ->andReturn(
            '{"sufficient": false, "refined_query": "try again"}',
            '{"sufficient": false, "refined_query": "try once more"}',
            'Best effort answer.',
        );

    app()->instance(PrismRetryHandler::class, $prism);

    $retriever = new AgenticRetriever(pipeline: $pipeline, maxSteps: 2);
    $result = $retriever->ask('Hard question?');

    expect($result->stepCount())->toBe(2)
        ->and($result->answer)->toBe('Best effort answer.');
});

test('ask handles malformed JSON from sufficiency eval', function () {
    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('retrieve')->andReturn(collect([
        ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'Data'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    // Returns non-JSON — json_decode returns null, defaults to sufficient=false
    $prism->shouldReceive('generate')
        ->andReturn(
            'not valid json at all',  // malformed — treated as insufficient
            '{"sufficient": true}',    // sufficient
            'Final answer.',
        );

    app()->instance(PrismRetryHandler::class, $prism);

    $retriever = new AgenticRetriever(pipeline: $pipeline, maxSteps: 3);
    $result = $retriever->ask('Question?');

    // Should not crash, should proceed with more retrieval steps
    expect($result)->toBeInstanceOf(AgenticResult::class)
        ->and($result->answer)->toBe('Final answer.');
});

test('ask deduplicates chunks across steps', function () {
    $pipeline = Mockery::mock(RagPipeline::class);
    // Returns same chunk both times
    $pipeline->shouldReceive('retrieve')->andReturn(collect([
        ['id' => 'same-id', 'score' => 0.9, 'metadata' => [], 'content' => 'Same chunk'],
    ]));

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('generate')
        ->andReturn(
            '{"sufficient": false, "refined_query": "refined"}',
            '{"sufficient": true}',
            'Answer.',
        );

    app()->instance(PrismRetryHandler::class, $prism);

    $retriever = new AgenticRetriever(pipeline: $pipeline, maxSteps: 3);
    $result = $retriever->ask('Q?');

    // Should have only 1 unique chunk despite 2 retrieval steps
    expect($result->totalChunksRetrieved)->toBe(1);
});

test('ask with maxSteps=1 does single retrieval', function () {
    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('retrieve')->once()->andReturn(collect());

    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('generate')
        ->andReturn('{"sufficient": false}', 'Answer.');

    app()->instance(PrismRetryHandler::class, $prism);

    $retriever = new AgenticRetriever(pipeline: $pipeline, maxSteps: 1);
    $result = $retriever->ask('Q?');

    expect($result->stepCount())->toBe(1);
});
