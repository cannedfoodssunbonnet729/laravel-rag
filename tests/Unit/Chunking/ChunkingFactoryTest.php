<?php

declare(strict_types=1);

use Moneo\LaravelRag\Chunking\ChunkingFactory;
use Moneo\LaravelRag\Chunking\Strategies\CharacterChunker;
use Moneo\LaravelRag\Chunking\Strategies\MarkdownChunker;
use Moneo\LaravelRag\Chunking\Strategies\SemanticChunker;
use Moneo\LaravelRag\Chunking\Strategies\SentenceChunker;

test('creates character chunker', function () {
    expect((new ChunkingFactory)->make('character'))->toBeInstanceOf(CharacterChunker::class);
});

test('creates sentence chunker', function () {
    expect((new ChunkingFactory)->make('sentence'))->toBeInstanceOf(SentenceChunker::class);
});

test('creates markdown chunker', function () {
    expect((new ChunkingFactory)->make('markdown'))->toBeInstanceOf(MarkdownChunker::class);
});

test('creates semantic chunker', function () {
    expect((new ChunkingFactory)->make('semantic'))->toBeInstanceOf(SemanticChunker::class);
});

test('throws on unknown strategy', function () {
    (new ChunkingFactory)->make('nonexistent');
})->throws(InvalidArgumentException::class, 'Unknown chunking strategy');

test('extend registers custom strategy', function () {
    $factory = new ChunkingFactory;
    $factory->extend('custom', CharacterChunker::class);

    expect($factory->make('custom'))->toBeInstanceOf(CharacterChunker::class);
});

test('extend overwrites existing strategy', function () {
    $factory = new ChunkingFactory;
    $factory->extend('character', SentenceChunker::class);

    expect($factory->make('character'))->toBeInstanceOf(SentenceChunker::class);
});

test('available lists all strategies', function () {
    $factory = new ChunkingFactory;

    expect($factory->available())->toContain('character', 'sentence', 'markdown', 'semantic');
});

test('available includes extended strategies', function () {
    $factory = new ChunkingFactory;
    $factory->extend('custom', CharacterChunker::class);

    expect($factory->available())->toContain('custom');
});
