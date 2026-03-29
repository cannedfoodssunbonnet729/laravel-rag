<?php

declare(strict_types=1);

use Moneo\LaravelRag\Memory\RagThread;

test('RagThread has correct table name', function () {
    $thread = new RagThread;

    expect($thread->getTable())->toBe('rag_threads');
});

test('RagThread has correct fillable fields', function () {
    $thread = new RagThread;

    expect($thread->getFillable())->toContain('model', 'title', 'metadata');
});

test('RagThread casts metadata to array', function () {
    $thread = new RagThread;
    $casts = $thread->getCasts();

    expect($casts['metadata'])->toBe('array');
});

test('estimateTokens approximates correctly', function () {
    $thread = new RagThread;
    $reflection = new ReflectionClass($thread);
    $method = $reflection->getMethod('estimateTokens');
    $method->setAccessible(true);

    // ~4 chars per token
    $tokens = $method->invoke($thread, str_repeat('a', 400));

    expect($tokens)->toBe(100);
});

test('estimateTokens rounds up', function () {
    $thread = new RagThread;
    $reflection = new ReflectionClass($thread);
    $method = $reflection->getMethod('estimateTokens');
    $method->setAccessible(true);

    $tokens = $method->invoke($thread, 'hi'); // 2 chars → ceil(2/4) = 1

    expect($tokens)->toBe(1);
});
