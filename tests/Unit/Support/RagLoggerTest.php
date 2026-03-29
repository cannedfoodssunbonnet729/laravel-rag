<?php

declare(strict_types=1);

use Moneo\LaravelRag\Support\RagLogger;

test('sanitiseContext hashes text fields', function () {
    $reflection = new ReflectionClass(RagLogger::class);
    $method = $reflection->getMethod('sanitiseContext');
    $method->setAccessible(true);

    $context = ['text' => 'sensitive user input', 'driver' => 'openai'];
    $sanitised = $method->invoke(null, $context);

    expect($sanitised)->not->toHaveKey('text')
        ->and($sanitised)->toHaveKey('text_hash')
        ->and($sanitised)->toHaveKey('text_length')
        ->and($sanitised['text_length'])->toBe(20)
        ->and($sanitised['driver'])->toBe('openai');
});

test('sanitiseContext hashes query field', function () {
    $reflection = new ReflectionClass(RagLogger::class);
    $method = $reflection->getMethod('sanitiseContext');
    $method->setAccessible(true);

    $context = ['query' => 'What is pgvector?'];
    $sanitised = $method->invoke(null, $context);

    expect($sanitised)->not->toHaveKey('query')
        ->and($sanitised)->toHaveKey('query_hash')
        ->and($sanitised)->toHaveKey('query_length');
});

test('sanitiseContext preserves non-text fields', function () {
    $reflection = new ReflectionClass(RagLogger::class);
    $method = $reflection->getMethod('sanitiseContext');
    $method->setAccessible(true);

    $context = ['limit' => 5, 'threshold' => 0.8, 'driver' => 'pgvector'];
    $sanitised = $method->invoke(null, $context);

    expect($sanitised)->toBe($context);
});

test('textHash returns 12-char hex string', function () {
    $hash = RagLogger::textHash('test input');

    expect($hash)->toHaveLength(12)
        ->and(ctype_xdigit($hash))->toBeTrue();
});

test('textHash is deterministic', function () {
    expect(RagLogger::textHash('same input'))->toBe(RagLogger::textHash('same input'));
});

test('textHash differs for different inputs', function () {
    expect(RagLogger::textHash('input a'))->not->toBe(RagLogger::textHash('input b'));
});

test('sanitiseContext handles empty context', function () {
    $reflection = new ReflectionClass(RagLogger::class);
    $method = $reflection->getMethod('sanitiseContext');
    $method->setAccessible(true);

    expect($method->invoke(null, []))->toBe([]);
});
