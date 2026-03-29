<?php

declare(strict_types=1);

use Moneo\LaravelRag\Chunking\Strategies\SemanticChunker;

test('returns empty for empty input', function () {
    $chunker = new SemanticChunker;

    expect($chunker->chunk(''))->toBeEmpty();
});

test('returns single chunk for single sentence', function () {
    $chunker = new SemanticChunker;
    $text = 'Just one sentence here.';

    expect($chunker->chunk($text))->toHaveCount(1);
});

test('cosineSimilarity returns 0 for zero vectors', function () {
    $chunker = new SemanticChunker;
    $reflection = new ReflectionClass($chunker);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);

    $result = $method->invoke($chunker, [0.0, 0.0, 0.0], [0.0, 0.0, 0.0]);

    expect($result)->toBe(0.0);
});

test('cosineSimilarity returns 1 for identical vectors', function () {
    $chunker = new SemanticChunker;
    $reflection = new ReflectionClass($chunker);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);

    $result = $method->invoke($chunker, [1.0, 0.0], [1.0, 0.0]);

    expect($result)->toBeGreaterThan(0.99);
});

test('cosineSimilarity returns -1 for opposite vectors', function () {
    $chunker = new SemanticChunker;
    $reflection = new ReflectionClass($chunker);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);

    $result = $method->invoke($chunker, [1.0, 0.0], [-1.0, 0.0]);

    expect($result)->toBeLessThan(-0.99);
});

test('splitBySize respects max size', function () {
    $chunker = new SemanticChunker;
    $reflection = new ReflectionClass($chunker);
    $method = $reflection->getMethod('splitBySize');
    $method->setAccessible(true);

    $sentences = ['Sentence one.', 'Sentence two.', 'Sentence three.', 'Sentence four.'];
    $result = $method->invoke($chunker, $sentences, 30);

    expect(count($result))->toBeGreaterThanOrEqual(2);
});

test('whitespace-only input returns empty', function () {
    $chunker = new SemanticChunker;

    expect($chunker->chunk('   '))->toBeEmpty();
});
