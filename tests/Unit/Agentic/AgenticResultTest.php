<?php

declare(strict_types=1);

use Moneo\LaravelRag\Agentic\AgenticResult;

test('counts steps correctly', function () {
    $result = new AgenticResult(
        answer: 'Answer',
        steps: [
            ['query' => 'q1', 'chunks_retrieved' => 5, 'sufficient' => false],
            ['query' => 'q2', 'chunks_retrieved' => 3, 'sufficient' => true],
        ],
        totalChunksRetrieved: 8,
        allChunks: collect(),
        totalTimeMs: 1500.0,
    );

    expect($result->stepCount())->toBe(2);
});

test('zero steps', function () {
    $result = new AgenticResult(
        answer: 'Answer',
        steps: [],
        totalChunksRetrieved: 0,
        allChunks: collect(),
        totalTimeMs: 0.0,
    );

    expect($result->stepCount())->toBe(0);
});

test('converts to array with correct keys', function () {
    $result = new AgenticResult(
        answer: 'Test',
        steps: [['query' => 'q', 'chunks_retrieved' => 5, 'sufficient' => true]],
        totalChunksRetrieved: 5,
        allChunks: collect(),
        totalTimeMs: 500.0,
    );

    $array = $result->toArray();

    expect($array)->toHaveKeys(['answer', 'steps', 'total_chunks_retrieved', 'step_count', 'total_time_ms'])
        ->and($array['step_count'])->toBe(1)
        ->and($array['total_time_ms'])->toBe(500.0);
});

test('readonly properties accessible', function () {
    $result = new AgenticResult(
        answer: 'A',
        steps: [],
        totalChunksRetrieved: 10,
        allChunks: collect([1, 2]),
        totalTimeMs: 99.9,
    );

    expect($result->answer)->toBe('A')
        ->and($result->totalChunksRetrieved)->toBe(10)
        ->and($result->allChunks)->toHaveCount(2)
        ->and($result->totalTimeMs)->toBe(99.9);
});
