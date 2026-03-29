<?php

declare(strict_types=1);

use Moneo\LaravelRag\Evals\RagEval;
use Moneo\LaravelRag\Pipeline\RagPipeline;

test('suite creates instance with default metrics', function () {
    $eval = RagEval::suite();
    $reflection = new ReflectionClass($eval);
    $metrics = $reflection->getProperty('metrics');
    $metrics->setAccessible(true);

    expect($metrics->getValue($eval))->toHaveCount(3);
});

test('add stores test cases', function () {
    $eval = RagEval::suite();
    $eval->add('Q1?', 'A1');
    $eval->add('Q2?', 'A2');

    $reflection = new ReflectionClass($eval);
    $cases = $reflection->getProperty('cases');
    $cases->setAccessible(true);

    expect($cases->getValue($eval))->toHaveCount(2);
});

test('using sets pipeline', function () {
    $eval = RagEval::suite();
    $pipeline = Mockery::mock(RagPipeline::class);
    $result = $eval->using($pipeline);

    expect($result)->toBe($eval);
});

test('run throws without pipeline', function () {
    $eval = RagEval::suite();
    $eval->add('Q?', 'A');
    $eval->run();
})->throws(\RuntimeException::class, 'No pipeline set');

test('withMetric adds custom metric', function () {
    $eval = RagEval::suite();
    $metric = Mockery::mock(\Moneo\LaravelRag\Evals\Metrics\MetricContract::class);
    $eval->withMetric($metric);

    $reflection = new ReflectionClass($eval);
    $metrics = $reflection->getProperty('metrics');
    $metrics->setAccessible(true);

    expect($metrics->getValue($eval))->toHaveCount(4); // 3 defaults + 1 custom
});

test('loadFromFile with array format', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'eval');
    file_put_contents($tmpFile, json_encode([
        ['question' => 'Q1?', 'expected' => 'A1'],
        ['question' => 'Q2?', 'expected' => 'A2'],
    ]));

    $eval = RagEval::suite();
    $eval->loadFromFile($tmpFile);

    $reflection = new ReflectionClass($eval);
    $cases = $reflection->getProperty('cases');
    $cases->setAccessible(true);

    expect($cases->getValue($eval))->toHaveCount(2);

    unlink($tmpFile);
});

test('loadFromFile with cases key format', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'eval');
    file_put_contents($tmpFile, json_encode([
        'cases' => [
            ['question' => 'Q1?', 'expected' => 'A1'],
        ],
    ]));

    $eval = RagEval::suite();
    $eval->loadFromFile($tmpFile);

    $reflection = new ReflectionClass($eval);
    $cases = $reflection->getProperty('cases');
    $cases->setAccessible(true);

    expect($cases->getValue($eval))->toHaveCount(1);

    unlink($tmpFile);
});
