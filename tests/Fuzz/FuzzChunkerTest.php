<?php

declare(strict_types=1);

use Moneo\LaravelRag\Chunking\Strategies\CharacterChunker;
use Moneo\LaravelRag\Chunking\Strategies\MarkdownChunker;
use Moneo\LaravelRag\Chunking\Strategies\SentenceChunker;

/**
 * Fuzz tests for chunking strategies.
 *
 * Each test sends random/malformed inputs to verify no crashes, hangs, or OOM.
 * Timeout: 2s per input enforced by PHPUnit/Pest timeout.
 *
 * @group fuzz
 */

test('CharacterChunker handles random byte strings without crashing', function () {
    $chunker = new CharacterChunker;

    for ($i = 0; $i < 500; $i++) {
        $text = random_bytes(random_int(1, 5000));
        $size = random_int(1, 2000);
        $overlap = random_int(0, max(0, $size - 1));

        try {
            $chunks = $chunker->chunk($text, ['size' => $size, 'overlap' => $overlap]);
            expect($chunks)->toBeArray();
        } catch (\InvalidArgumentException) {
            // Expected for overlap >= size
        }
    }
});

test('SentenceChunker handles random byte strings without crashing', function () {
    $chunker = new SentenceChunker;

    for ($i = 0; $i < 500; $i++) {
        $text = random_bytes(random_int(1, 3000));
        $size = random_int(1, 2000);

        $chunks = $chunker->chunk($text, ['size' => $size]);
        expect($chunks)->toBeArray();
    }
});

test('MarkdownChunker handles random byte strings without crashing', function () {
    $chunker = new MarkdownChunker;

    for ($i = 0; $i < 500; $i++) {
        $text = random_bytes(random_int(1, 3000));
        $size = random_int(1, 2000);

        $chunks = $chunker->chunk($text, ['size' => $size]);
        expect($chunks)->toBeArray();
    }
});

test('CharacterChunker handles null bytes and control characters', function () {
    $chunker = new CharacterChunker;

    $inputs = [
        "\x00\x00\x00",
        "\xff\xfe\xfd",
        str_repeat("\n", 1000),
        str_repeat("\t", 1000),
        str_repeat("\r\n", 500),
        "\xE2\x80\x8F".str_repeat("a", 100), // RTL marker
        "\xE2\x80\x8B".str_repeat("b", 100), // Zero-width space
    ];

    foreach ($inputs as $input) {
        $chunks = $chunker->chunk($input, ['size' => 50, 'overlap' => 10]);
        expect($chunks)->toBeArray();
    }
});

test('chunkers do not allocate excessive memory', function () {
    $chunker = new CharacterChunker;
    $text = random_bytes(100000); // 100KB

    $before = memory_get_usage(true);
    $chunks = $chunker->chunk($text, ['size' => 500, 'overlap' => 50]);
    $after = memory_get_usage(true);

    // Should not allocate more than 10x the input size
    expect($after - $before)->toBeLessThan(10 * strlen($text));
});
