<?php

declare(strict_types=1);

use Moneo\LaravelRag\Chunking\Strategies\SemanticChunker;
use Moneo\LaravelRag\Support\PrismRetryHandler;

test('chunk splits text by semantic similarity', function () {
    // Mock embeddings: first two sentences similar, third different
    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')
        ->andReturn(
            [0.9, 0.1, 0.0],  // sentence 1
            [0.85, 0.15, 0.0], // sentence 2 (similar to 1)
            [0.0, 0.1, 0.9],  // sentence 3 (very different)
            [0.05, 0.1, 0.85], // sentence 4 (similar to 3)
        );
    app()->instance(PrismRetryHandler::class, $prism);

    $chunker = new SemanticChunker;
    $text = 'First sentence about PHP. Second sentence about PHP too. Third sentence about cooking. Fourth about cooking also.';

    $chunks = $chunker->chunk($text, ['threshold' => 0.5, 'size' => 5000]);

    expect($chunks)->not->toBeEmpty()
        ->and(count($chunks))->toBeGreaterThanOrEqual(2);
});

test('chunk returns single chunk when all sentences are similar', function () {
    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')
        ->andReturn(
            [0.9, 0.1, 0.0],
            [0.88, 0.12, 0.0],
            [0.87, 0.13, 0.0],
        );
    app()->instance(PrismRetryHandler::class, $prism);

    $chunker = new SemanticChunker;
    $text = 'All about PHP. More about PHP. Even more PHP.';

    $chunks = $chunker->chunk($text, ['threshold' => 0.5, 'size' => 5000]);

    expect($chunks)->toHaveCount(1);
});

test('chunk respects max size', function () {
    $prism = Mockery::mock(PrismRetryHandler::class);
    $prism->shouldReceive('embed')->andReturn(
        [0.9, 0.1, 0.0],
        [0.0, 0.1, 0.9], // very different = split point
    );
    app()->instance(PrismRetryHandler::class, $prism);

    $chunker = new SemanticChunker;
    $text = str_repeat('A long sentence here. ', 20) . 'Completely different topic.';

    $chunks = $chunker->chunk($text, ['threshold' => 0.5, 'size' => 100]);

    foreach ($chunks as $chunk) {
        expect(mb_strlen($chunk))->toBeLessThanOrEqual(200); // Allow some overflow at sentence boundaries
    }
});
