<?php

declare(strict_types=1);

use Moneo\LaravelRag\Chunking\Strategies\SentenceChunker;

test('chunks text by sentences', function () {
    $chunker = new SentenceChunker;
    $text = 'First sentence. Second sentence. Third sentence. Fourth sentence.';
    $chunks = $chunker->chunk($text, ['size' => 40, 'max_sentences' => 2]);

    expect(count($chunks))->toBeGreaterThanOrEqual(2);
});

test('keeps short text as single chunk', function () {
    $chunker = new SentenceChunker;
    $chunks = $chunker->chunk('One sentence.', ['size' => 500]);

    expect($chunks)->toHaveCount(1);
});

test('handles text without sentence terminators', function () {
    $chunker = new SentenceChunker;
    $chunks = $chunker->chunk('No sentence terminators here');

    expect($chunks)->toHaveCount(1);
});

test('returns empty for empty input', function () {
    expect((new SentenceChunker)->chunk(''))->toBeEmpty();
});

test('handles text with only punctuation', function () {
    $chunker = new SentenceChunker;
    $chunks = $chunker->chunk('... !!! ???');

    expect($chunks)->not->toBeEmpty();
});

test('respects max size across sentences', function () {
    $chunker = new SentenceChunker;
    $text = 'Short. Also short. This is a slightly longer sentence for testing. Another one.';
    $chunks = $chunker->chunk($text, ['size' => 30]);

    foreach ($chunks as $chunk) {
        // Allow slight overflow due to sentence boundaries
        expect(mb_strlen($chunk))->toBeLessThanOrEqual(100);
    }
});

test('handles exclamation and question marks as terminators', function () {
    $chunker = new SentenceChunker;
    $text = 'Hello! How are you? I am fine. Great!';
    $chunks = $chunker->chunk($text, ['size' => 20]);

    expect(count($chunks))->toBeGreaterThanOrEqual(2);
});

test('whitespace-only text returns empty', function () {
    $chunker = new SentenceChunker;

    expect($chunker->chunk('   '))->toBeEmpty();
});
