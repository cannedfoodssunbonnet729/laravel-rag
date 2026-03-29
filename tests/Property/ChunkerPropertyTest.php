<?php

declare(strict_types=1);

use Moneo\LaravelRag\Chunking\Strategies\CharacterChunker;

/**
 * Property-based tests for CharacterChunker.
 *
 * These properties must hold for ALL valid inputs, not just specific examples.
 * Each test generates RAG_ERIS_ITERATIONS (default 1000) random inputs.
 */

$iterations = (int) (getenv('RAG_ERIS_ITERATIONS') ?: 1000);

test('no data loss: joining chunks contains all original characters', function () use ($iterations) {
    $chunker = new CharacterChunker;

    for ($i = 0; $i < $iterations; $i++) {
        $length = random_int(1, 5000);
        $text = str_repeat('a', random_int(0, 26))
            .bin2hex(random_bytes(random_int(1, (int) ceil($length / 2))));
        $text = mb_substr($text, 0, $length);

        $size = random_int(10, 1000);
        $overlap = random_int(0, $size - 1);

        $chunks = $chunker->chunk($text, ['size' => $size, 'overlap' => $overlap]);

        if (empty(trim($text))) {
            expect($chunks)->toBeEmpty();
            continue;
        }

        $joined = implode('', $chunks);
        // Every character in the original must appear in the chunks
        for ($c = 0; $c < mb_strlen(trim($text)); $c++) {
            $char = mb_substr(trim($text), $c, 1);
            expect(mb_strpos($joined, $char))->not->toBeFalse(
                "Character '{$char}' at position {$c} lost during chunking (size={$size}, overlap={$overlap})"
            );
        }
    }
});

test('every chunk length <= size for all inputs', function () use ($iterations) {
    $chunker = new CharacterChunker;

    for ($i = 0; $i < $iterations; $i++) {
        $text = bin2hex(random_bytes(random_int(1, 2500)));
        $size = random_int(10, 1000);
        $overlap = random_int(0, $size - 1);

        $chunks = $chunker->chunk($text, ['size' => $size, 'overlap' => $overlap]);

        foreach ($chunks as $idx => $chunk) {
            expect(mb_strlen($chunk))->toBeLessThanOrEqual($size,
                "Chunk #{$idx} exceeds size {$size}: length=".mb_strlen($chunk)
            );
        }
    }
});

test('empty input always produces empty output', function () use ($iterations) {
    $chunker = new CharacterChunker;

    $emptyInputs = ['', ' ', '  ', "\t", "\n", "  \n  \t  "];

    foreach ($emptyInputs as $input) {
        expect($chunker->chunk($input, ['size' => 100, 'overlap' => 10]))->toBeEmpty();
    }
});
