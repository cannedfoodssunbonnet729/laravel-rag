<?php

declare(strict_types=1);

use Moneo\LaravelRag\Chunking\Strategies\CharacterChunker;

dataset('chunk_edge_cases', [
    'empty string' => ['', 500, 50, []],
    'single character' => ['a', 500, 50, ['a']],
    'whitespace only' => ['   ', 500, 50, []],
    'exactly chunk size' => [str_repeat('a', 500), 500, 0, [str_repeat('a', 500)]],
]);

test('chunks text by character size', function () {
    $chunker = new CharacterChunker;
    $text = str_repeat('a', 1000);
    $chunks = $chunker->chunk($text, ['size' => 300, 'overlap' => 0]);

    expect($chunks)->toHaveCount(4)
        ->and(mb_strlen($chunks[0]))->toBe(300);
});

test('respects overlap setting', function () {
    $chunker = new CharacterChunker;
    $text = str_repeat('a', 500);
    $chunks = $chunker->chunk($text, ['size' => 300, 'overlap' => 100]);

    expect(count($chunks))->toBeGreaterThanOrEqual(2);
});

test('handles edge cases correctly', function (string $text, int $size, int $overlap, array $expected) {
    $chunker = new CharacterChunker;
    $chunks = $chunker->chunk($text, ['size' => $size, 'overlap' => $overlap]);

    expect($chunks)->toBe($expected);
})->with('chunk_edge_cases');

test('throws when overlap >= size', function () {
    $chunker = new CharacterChunker;
    $chunker->chunk('hello world this is test', ['size' => 5, 'overlap' => 5]);
})->throws(\InvalidArgumentException::class, 'Overlap');

test('throws when overlap > size', function () {
    $chunker = new CharacterChunker;
    $chunker->chunk('some text', ['size' => 5, 'overlap' => 10]);
})->throws(\InvalidArgumentException::class, 'Overlap');

test('handles unicode multibyte text', function () {
    $chunker = new CharacterChunker;
    $text = str_repeat('日', 100); // Japanese characters
    $chunks = $chunker->chunk($text, ['size' => 30, 'overlap' => 0]);

    expect(count($chunks))->toBeGreaterThanOrEqual(3);
    foreach ($chunks as $chunk) {
        expect(mb_strlen($chunk))->toBeLessThanOrEqual(30);
    }
});

test('uses config defaults when no options provided', function () {
    config(['rag.ingest.chunk_size' => 100, 'rag.ingest.chunk_overlap' => 10]);
    $chunker = new CharacterChunker;
    $text = str_repeat('x', 250);
    $chunks = $chunker->chunk($text);

    expect(count($chunks))->toBeGreaterThanOrEqual(2);
});

test('returns single chunk for text shorter than size', function () {
    $chunker = new CharacterChunker;
    $chunks = $chunker->chunk('Hello world', ['size' => 500, 'overlap' => 0]);

    expect($chunks)->toHaveCount(1)->and($chunks[0])->toBe('Hello world');
});

test('one over chunk size produces two chunks', function () {
    $chunker = new CharacterChunker;
    $text = str_repeat('a', 501);
    $chunks = $chunker->chunk($text, ['size' => 500, 'overlap' => 0]);

    expect($chunks)->toHaveCount(2);
});

test('no empty chunks in output', function () {
    $chunker = new CharacterChunker;
    $text = str_repeat('x', 1000);
    $chunks = $chunker->chunk($text, ['size' => 300, 'overlap' => 50]);

    foreach ($chunks as $chunk) {
        expect(trim($chunk))->not->toBe('');
    }
});
