<?php

declare(strict_types=1);

use Moneo\LaravelRag\DevTools\DebugbarCollector;

test('getName returns rag', function () {
    if (! class_exists(\DebugBar\DataCollector\DataCollector::class)) {
        $this->markTestSkipped('Debugbar not installed');
    }

    $collector = new DebugbarCollector;
    expect($collector->getName())->toBe('rag');
});

test('collect returns expected keys', function () {
    if (! class_exists(\DebugBar\DataCollector\DataCollector::class)) {
        $this->markTestSkipped('Debugbar not installed');
    }

    $collector = new DebugbarCollector;
    $data = $collector->collect();

    expect($data)->toHaveKeys([
        'query_count',
        'chunks_retrieved',
        'embeddings_generated',
        'cache_hits',
        'cache_misses',
        'cache_hit_rate',
        'retrieval_ms',
        'generation_ms',
    ]);
});

test('recordQuery increments counters', function () {
    if (! class_exists(\DebugBar\DataCollector\DataCollector::class)) {
        $this->markTestSkipped('Debugbar not installed');
    }

    $collector = new DebugbarCollector;
    $collector->recordQuery(5, 100.0, 200.0);
    $collector->recordQuery(3, 50.0, 150.0);

    $data = $collector->collect();

    expect($data['query_count'])->toBe(2)
        ->and($data['chunks_retrieved'])->toBe(8);
});
