<?php

declare(strict_types=1);

use Moneo\LaravelRag\Streaming\RagStream;

test('getSources formats chunk metadata correctly', function () {
    $stream = new RagStream(
        question: 'test?',
        context: 'context',
        chunks: collect([
            ['id' => '1', 'score' => 0.9, 'metadata' => ['source' => 'doc.pdf'], 'content' => 'Test content here'],
            ['id' => '2', 'score' => 0.7, 'metadata' => [], 'content' => 'Other content'],
        ]),
        systemPrompt: null,
        provider: 'openai',
        model: 'gpt-4o',
    );

    $reflection = new ReflectionClass($stream);
    $method = $reflection->getMethod('getSources');
    $method->setAccessible(true);

    $sources = $method->invoke($stream);

    expect($sources)->toHaveCount(2)
        ->and($sources[0]['source'])->toBe('doc.pdf')
        ->and($sources[0]['score'])->toBe(0.9)
        ->and($sources[1]['source'])->toBe('Unknown');
});

test('getSources truncates preview to 200 chars', function () {
    $longContent = str_repeat('x', 500);

    $stream = new RagStream(
        question: 'q',
        context: 'c',
        chunks: collect([['id' => '1', 'score' => 0.5, 'metadata' => [], 'content' => $longContent]]),
        systemPrompt: null,
        provider: 'openai',
        model: 'gpt-4',
    );

    $reflection = new ReflectionClass($stream);
    $method = $reflection->getMethod('getSources');
    $method->setAccessible(true);

    $sources = $method->invoke($stream);

    expect(mb_strlen($sources[0]['preview']))->toBe(200);
});

test('toStreamedResponse returns StreamedResponse with SSE headers', function () {
    $stream = new RagStream(
        question: 'test?',
        context: 'ctx',
        chunks: collect(),
        systemPrompt: 'prompt',
        provider: 'openai',
        model: 'gpt-4',
    );

    $response = $stream->toStreamedResponse();

    expect($response)->toBeInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class)
        ->and($response->headers->get('Content-Type'))->toBe('text/event-stream')
        ->and($response->headers->get('Cache-Control'))->toContain('no-cache');
});

test('getSources handles empty chunks', function () {
    $stream = new RagStream(
        question: 'q',
        context: 'c',
        chunks: collect(),
        systemPrompt: null,
        provider: 'openai',
        model: 'gpt-4',
    );

    $reflection = new ReflectionClass($stream);
    $method = $reflection->getMethod('getSources');
    $method->setAccessible(true);

    expect($method->invoke($stream))->toBeEmpty();
});
