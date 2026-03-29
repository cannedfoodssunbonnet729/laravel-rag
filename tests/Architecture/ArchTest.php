<?php

declare(strict_types=1);

arch('strict types in every file')
    ->expect('Moneo\LaravelRag')
    ->toUseStrictTypes();

arch('no debug statements in source')
    ->expect('Moneo\LaravelRag')
    ->not->toUse(['dd', 'dump', 'var_dump', 'print_r', 'ray']);

arch('contracts are interfaces only')
    ->expect('Moneo\LaravelRag\VectorStores\Contracts')
    ->toBeInterfaces();

arch('commands extend correct base')
    ->expect('Moneo\LaravelRag\Commands')
    ->toExtend('Illuminate\Console\Command');

arch('chunking strategies implement contract')
    ->expect('Moneo\LaravelRag\Chunking\Strategies\CharacterChunker')
    ->toImplement('Moneo\LaravelRag\Chunking\Strategies\ChunkerContract');

arch('sentence chunker implements contract')
    ->expect('Moneo\LaravelRag\Chunking\Strategies\SentenceChunker')
    ->toImplement('Moneo\LaravelRag\Chunking\Strategies\ChunkerContract');

arch('markdown chunker implements contract')
    ->expect('Moneo\LaravelRag\Chunking\Strategies\MarkdownChunker')
    ->toImplement('Moneo\LaravelRag\Chunking\Strategies\ChunkerContract');

arch('semantic chunker implements contract')
    ->expect('Moneo\LaravelRag\Chunking\Strategies\SemanticChunker')
    ->toImplement('Moneo\LaravelRag\Chunking\Strategies\ChunkerContract');

arch('eval metrics implement contract')
    ->expect('Moneo\LaravelRag\Evals\Metrics\FaithfulnessMetric')
    ->toImplement('Moneo\LaravelRag\Evals\Metrics\MetricContract');

arch('facades extend Laravel facade')
    ->expect('Moneo\LaravelRag\Facades')
    ->toExtend('Illuminate\Support\Facades\Facade');

arch('security classes have no external framework dependencies')
    ->expect('Moneo\LaravelRag\Security')
    ->toOnlyUse([
        'Moneo\LaravelRag\Security',
    ]);

arch('no core classes depend on Filament')
    ->expect('Moneo\LaravelRag\Pipeline')
    ->not->toUse('Moneo\LaravelRag\Filament');
