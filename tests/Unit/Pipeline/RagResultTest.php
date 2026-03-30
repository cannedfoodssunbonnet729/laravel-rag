<?php

declare(strict_types=1);

use Moneo\LaravelRag\Pipeline\RagResult;

test('calculates total time', function () {
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect(),
        question: 'Q?',
        retrievalTimeMs: 100.5,
        generationTimeMs: 200.3,
    );

    expect($result->totalTimeMs())->toBe(300.8);
});

test('extracts sources from chunks', function () {
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect([
            ['id' => '1', 'score' => 0.9, 'metadata' => ['source' => 'doc.pdf'], 'content' => 'Content 1'],
            ['id' => '2', 'score' => 0.8, 'metadata' => ['source' => 'guide.md'], 'content' => 'Content 2'],
        ]),
        question: 'Q?',
        retrievalTimeMs: 50,
        generationTimeMs: 150,
    );

    $sources = $result->sources();

    expect($sources)->toHaveCount(2)
        ->and($sources->first()['source'])->toBe('doc.pdf')
        ->and($sources->first()['score'])->toBe(0.9);
});

test('sources returns Unknown when metadata missing source', function () {
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect([['id' => '1', 'score' => 0.5, 'metadata' => [], 'content' => 'C']]),
        question: 'Q?',
        retrievalTimeMs: 10,
        generationTimeMs: 20,
    );

    expect($result->sources()->first()['source'])->toBe('Unknown');
});

test('sources preview truncates at 200 chars', function () {
    $longContent = str_repeat('x', 500);
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect([['id' => '1', 'score' => 0.5, 'metadata' => [], 'content' => $longContent]]),
        question: 'Q?',
        retrievalTimeMs: 10,
        generationTimeMs: 20,
    );

    expect(mb_strlen($result->sources()->first()['preview']))->toBe(200);
});

test('converts to array with correct keys', function () {
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect(),
        question: 'Q?',
        retrievalTimeMs: 50,
        generationTimeMs: 150,
    );

    $array = $result->toArray();

    expect($array)->toHaveKeys(['answer', 'question', 'sources', 'timing'])
        ->and($array['timing'])->toHaveKeys(['retrieval_ms', 'generation_ms', 'total_ms'])
        ->and($array['timing']['total_ms'])->toBe(200.0);
});

test('empty chunks returns empty sources', function () {
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect(),
        question: 'Q?',
        retrievalTimeMs: 0,
        generationTimeMs: 0,
    );

    expect($result->sources())->toBeEmpty();
});

test('sourceModels returns empty for chunks without model metadata', function () {
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect([
            ['id' => '1', 'score' => 0.9, 'metadata' => [], 'content' => 'No model info'],
        ]),
        question: 'Q?',
        retrievalTimeMs: 0,
        generationTimeMs: 0,
    );

    expect($result->sourceModels())->toBeEmpty();
});

test('sourceModels returns empty for empty chunks', function () {
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect(),
        question: 'Q?',
        retrievalTimeMs: 0,
        generationTimeMs: 0,
    );

    expect($result->sourceModels())->toBeEmpty();
});

test('sourceModels filters out nonexistent model classes', function () {
    $result = new RagResult(
        answer: 'Answer',
        chunks: collect([
            ['id' => '1', 'score' => 0.9, 'metadata' => ['model' => 'App\\Models\\NonExistent', 'id' => 1], 'content' => 'X'],
        ]),
        question: 'Q?',
        retrievalTimeMs: 0,
        generationTimeMs: 0,
    );

    expect($result->sourceModels())->toBeEmpty();
});
