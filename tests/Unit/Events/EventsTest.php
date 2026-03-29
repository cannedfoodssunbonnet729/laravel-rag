<?php

declare(strict_types=1);

use Moneo\LaravelRag\Events\EmbeddingCacheHit;
use Moneo\LaravelRag\Events\EmbeddingGenerated;

test('EmbeddingCacheHit accepts null model', function () {
    $event = new EmbeddingCacheHit(null, 'test text');

    expect($event->model)->toBeNull()
        ->and($event->sourceText)->toBe('test text');
});

test('EmbeddingGenerated accepts null model', function () {
    $event = new EmbeddingGenerated(null, 'test text', [0.1, 0.2]);

    expect($event->model)->toBeNull()
        ->and($event->sourceText)->toBe('test text')
        ->and($event->vector)->toBe([0.1, 0.2]);
});

test('EmbeddingGenerated stores vector correctly', function () {
    $vector = array_fill(0, 1536, 0.01);
    $event = new EmbeddingGenerated(null, 'text', $vector);

    expect($event->vector)->toHaveCount(1536);
});
