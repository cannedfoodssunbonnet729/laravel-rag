<?php

declare(strict_types=1);

use Moneo\LaravelRag\Chunking\Strategies\MarkdownChunker;

test('splits by markdown headers', function () {
    $chunker = new MarkdownChunker;
    $text = "# Title\n\nIntro.\n\n## Section 1\n\nContent 1.\n\n## Section 2\n\nContent 2.";
    $chunks = $chunker->chunk($text, ['size' => 5000]);

    expect(count($chunks))->toBeGreaterThanOrEqual(2);
});

test('handles text without headers', function () {
    $chunker = new MarkdownChunker;
    $chunks = $chunker->chunk('Just plain text.');

    expect($chunks)->toHaveCount(1);
});

test('further splits large sections by paragraphs', function () {
    $chunker = new MarkdownChunker;
    $text = "# Big\n\n".str_repeat("Paragraph. ", 200);
    $chunks = $chunker->chunk($text, ['size' => 200]);

    expect(count($chunks))->toBeGreaterThan(1);
});

test('returns empty for empty input', function () {
    expect((new MarkdownChunker)->chunk(''))->toBeEmpty();
});

test('handles nested header levels', function () {
    $chunker = new MarkdownChunker;
    $text = "# H1\n\nContent.\n\n## H2\n\nContent.\n\n### H3\n\nContent.";
    $chunks = $chunker->chunk($text, ['size' => 5000]);

    expect(count($chunks))->toBeGreaterThanOrEqual(2);
});

test('handles code blocks with hash characters', function () {
    $chunker = new MarkdownChunker;
    $text = "# Title\n\n```\n# This is a comment in code\necho hello\n```\n\n## Next";
    $chunks = $chunker->chunk($text, ['size' => 5000]);

    expect(count($chunks))->toBeGreaterThanOrEqual(1);
});

test('does not produce empty chunks', function () {
    $chunker = new MarkdownChunker;
    $text = "# Title\n\n\n\n## Section\n\nContent.";
    $chunks = $chunker->chunk($text, ['size' => 5000]);

    foreach ($chunks as $chunk) {
        expect(trim($chunk))->not->toBe('');
    }
});
