<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Evals;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
class EvalReport implements Arrayable
{
    /**
     * @param  array<int, array{question: string, expected: string, answer: string, scores: array<string, float>, latency_ms: float}>  $results  Individual test case results
     * @param  array<string, float>  $averageScores  Average scores per metric
     * @param  float  $totalTimeMs  Total evaluation time
     */
    public function __construct(
        public readonly array $results,
        public readonly array $averageScores,
        public readonly float $totalTimeMs,
    ) {}

    /**
     * Check if all metrics pass a minimum threshold.
     */
    public function passes(float $minScore = 0.8): bool
    {
        foreach ($this->averageScores as $score) {
            if ($score < $minScore) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the number of test cases.
     */
    public function count(): int
    {
        return count($this->results);
    }

    /**
     * Format as a CLI table.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    public function toTable(): array
    {
        $metricNames = array_keys($this->averageScores);
        $headers = array_merge(['#', 'Question'], $metricNames, ['Latency']);

        $rows = [];
        foreach ($this->results as $index => $result) {
            $row = [
                $index + 1,
                mb_substr($result['question'], 0, 50),
            ];

            foreach ($metricNames as $metric) {
                $score = $result['scores'][$metric] ?? 0;
                $row[] = number_format($score, 2);
            }

            $row[] = number_format($result['latency_ms'], 0).'ms';
            $rows[] = $row;
        }

        // Add average row
        $avgRow = ['', 'AVERAGE'];
        foreach ($metricNames as $metric) {
            $avgRow[] = number_format($this->averageScores[$metric], 2);
        }
        $avgRow[] = number_format($this->totalTimeMs / max(count($this->results), 1), 0).'ms';
        $rows[] = $avgRow;

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'results' => $this->results,
            'average_scores' => $this->averageScores,
            'total_time_ms' => round($this->totalTimeMs, 2),
            'passes' => $this->passes(),
        ];
    }

    /**
     * Export as JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
