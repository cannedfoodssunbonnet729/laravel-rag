<?php

declare(strict_types=1);

test('validates embedding dimensions > 0', function () {
    config(['rag.embedding.dimensions' => 0]);

    // Re-run validation
    $sp = new \Moneo\LaravelRag\RagServiceProvider(app());
    $ref = new ReflectionClass($sp);
    $m = $ref->getMethod('validateConfig');
    $m->setAccessible(true);
    $m->invoke($sp);
})->throws(\InvalidArgumentException::class, 'dimensions must be > 0');

test('validates chunk_size > 0', function () {
    config(['rag.ingest.chunk_size' => 0]);

    $sp = new \Moneo\LaravelRag\RagServiceProvider(app());
    $ref = new ReflectionClass($sp);
    $m = $ref->getMethod('validateConfig');
    $m->setAccessible(true);
    $m->invoke($sp);
})->throws(\InvalidArgumentException::class, 'chunk_size must be > 0');

test('validates chunk_overlap < chunk_size', function () {
    config(['rag.ingest.chunk_overlap' => 500, 'rag.ingest.chunk_size' => 500]);

    $sp = new \Moneo\LaravelRag\RagServiceProvider(app());
    $ref = new ReflectionClass($sp);
    $m = $ref->getMethod('validateConfig');
    $m->setAccessible(true);
    $m->invoke($sp);
})->throws(\InvalidArgumentException::class, 'chunk_overlap');

test('validates default_limit > 0', function () {
    config(['rag.search.default_limit' => 0]);

    $sp = new \Moneo\LaravelRag\RagServiceProvider(app());
    $ref = new ReflectionClass($sp);
    $m = $ref->getMethod('validateConfig');
    $m->setAccessible(true);
    $m->invoke($sp);
})->throws(\InvalidArgumentException::class, 'default_limit must be > 0');

test('valid config passes validation', function () {
    $sp = new \Moneo\LaravelRag\RagServiceProvider(app());
    $ref = new ReflectionClass($sp);
    $m = $ref->getMethod('validateConfig');
    $m->setAccessible(true);
    $m->invoke($sp);

    expect(true)->toBeTrue(); // No exception thrown
});
