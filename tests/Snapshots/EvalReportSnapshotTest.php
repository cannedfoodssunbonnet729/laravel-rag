<?php

declare(strict_types=1);

use Moneo\LaravelRag\Evals\EvalReport;

test('eval report JSON structure is stable', function () {
    $report = new EvalReport(
        results: [
            [
                'question' => 'What is RAG?',
                'expected' => 'Retrieval Augmented Generation',
                'answer' => 'RAG is a technique...',
                'scores' => ['faithfulness' => 0.95, 'relevancy' => 0.88, 'context_recall' => 0.92],
                'latency_ms' => 450.5,
            ],
        ],
        averageScores: ['faithfulness' => 0.95, 'relevancy' => 0.88, 'context_recall' => 0.92],
        totalTimeMs: 450.5,
    );

    $json = json_decode($report->toJson(), true);

    // Verify structure keys are present
    expect($json)->toHaveKeys(['results', 'average_scores', 'total_time_ms', 'passes'])
        ->and($json['results'][0])->toHaveKeys(['question', 'expected', 'answer', 'scores', 'latency_ms'])
        ->and($json['average_scores'])->toHaveKeys(['faithfulness', 'relevancy', 'context_recall']);
});

test('eval report table structure is stable', function () {
    $report = new EvalReport(
        results: [
            ['question' => 'Q?', 'expected' => 'A', 'answer' => 'A', 'scores' => ['m1' => 0.9], 'latency_ms' => 100],
        ],
        averageScores: ['m1' => 0.9],
        totalTimeMs: 100,
    );

    $table = $report->toTable();

    expect($table['headers'])->toBe(['#', 'Question', 'm1', 'Latency'])
        ->and($table['rows'])->toHaveCount(2); // 1 result + 1 average
});
