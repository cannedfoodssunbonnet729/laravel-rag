<?php

declare(strict_types=1);

use Moneo\LaravelRag\Facades\Ingest;
use Moneo\LaravelRag\Facades\Rag;
use Moneo\LaravelRag\Facades\RagEval;

test('rag facade resolves pipeline', function () {
    $pipeline = Rag::from('App\\Models\\Document');

    expect($pipeline)->toBeInstanceOf(\Moneo\LaravelRag\Pipeline\RagPipeline::class);
});

test('ingest facade resolves pipeline', function () {
    $pipeline = Ingest::text('Hello world');

    expect($pipeline)->toBeInstanceOf(\Moneo\LaravelRag\Pipeline\IngestPipeline::class);
});

test('rag eval facade resolves eval', function () {
    $eval = RagEval::suite();

    expect($eval)->toBeInstanceOf(\Moneo\LaravelRag\Evals\RagEval::class);
});

test('rag pipeline from facade is immutable', function () {
    $p1 = Rag::from('App\\Models\\A');
    $p2 = Rag::from('App\\Models\\B');

    expect($p1)->not->toBe($p2);
});
