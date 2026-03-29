<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Evals\Metrics;

interface MetricContract
{
    /**
     * Get the metric name.
     */
    public function name(): string;

    /**
     * Evaluate the metric and return a score between 0.0 and 1.0.
     *
     * @param  string  $question  The original question
     * @param  string  $answer  The generated answer
     * @param  string  $expected  The expected/reference answer
     * @param  string  $context  The retrieved context
     */
    public function evaluate(string $question, string $answer, string $expected, string $context): float;
}
