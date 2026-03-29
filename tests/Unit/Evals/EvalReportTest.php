<?php

declare(strict_types=1);

use Moneo\LaravelRag\Evals\EvalReport;

test('passes when all scores above threshold', function () {
    $report = new EvalReport([], ['faithfulness' => 0.9, 'relevancy' => 0.85], 1000);

    expect($report->passes(0.8))->toBeTrue();
});

test('fails when any score below threshold', function () {
    $report = new EvalReport([], ['faithfulness' => 0.9, 'relevancy' => 0.6], 1000);

    expect($report->passes(0.8))->toBeFalse();
});

test('passes with empty scores', function () {
    $report = new EvalReport([], [], 100);

    expect($report->passes(0.8))->toBeTrue();
});

test('generates table output', function () {
    $report = new EvalReport(
        results: [['question' => 'Q?', 'expected' => 'A', 'answer' => 'A', 'scores' => ['f' => 0.9], 'latency_ms' => 500]],
        averageScores: ['f' => 0.9],
        totalTimeMs: 500,
    );

    $table = $report->toTable();

    expect($table)->toHaveKeys(['headers', 'rows'])
        ->and($table['headers'])->toContain('f');
});

test('exports as JSON', function () {
    $report = new EvalReport([], ['faithfulness' => 0.9], 100);
    $json = $report->toJson();
    $decoded = json_decode($json, true);

    expect($decoded)->toHaveKeys(['results', 'average_scores', 'total_time_ms', 'passes']);
});

test('counts test cases', function () {
    $report = new EvalReport(
        results: [
            ['question' => 'Q1', 'expected' => 'A1', 'answer' => 'A', 'scores' => [], 'latency_ms' => 100],
            ['question' => 'Q2', 'expected' => 'A2', 'answer' => 'A', 'scores' => [], 'latency_ms' => 100],
        ],
        averageScores: [],
        totalTimeMs: 200,
    );

    expect($report->count())->toBe(2);
});

test('toArray rounds timing values', function () {
    $report = new EvalReport([], [], 123.456789);
    $array = $report->toArray();

    expect($array['total_time_ms'])->toBe(123.46);
});

test('zero results count is zero', function () {
    $report = new EvalReport([], [], 0);

    expect($report->count())->toBe(0);
});

test('table includes average row', function () {
    $report = new EvalReport(
        results: [['question' => 'Q', 'expected' => 'A', 'answer' => 'A', 'scores' => ['m' => 0.8], 'latency_ms' => 100]],
        averageScores: ['m' => 0.8],
        totalTimeMs: 100,
    );

    $table = $report->toTable();
    $lastRow = end($table['rows']);

    expect($lastRow[1])->toBe('AVERAGE');
});
