<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Evals\Metrics;

use Moneo\LaravelRag\Support\PrismRetryHandler;
use Moneo\LaravelRag\Support\ScoreParser;

class ContextRecallMetric implements MetricContract
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'context_recall';
    }

    /**
     * Evaluate whether the retrieved context contains the information needed to answer.
     *
     * A score of 1.0 means the context contains all necessary information.
     * A score of 0.0 means the context is missing all necessary information.
     */
    public function evaluate(string $question, string $answer, string $expected, string $context): float
    {
        $provider = config('rag.llm.provider');
        $model = config('rag.llm.model');

        $response = app(PrismRetryHandler::class)->generate($provider, $model, 'You are an evaluation judge. Assess the CONTEXT RECALL — whether the retrieved context contains the information needed to produce the expected answer.

Score on a scale of 0.0 to 1.0:
- 1.0: The context contains ALL information needed to produce the expected answer
- 0.5: The context contains SOME of the needed information
- 0.0: The context contains NONE of the needed information

Respond with ONLY a decimal number between 0.0 and 1.0.', "Question: {$question}\n\nExpected answer: {$expected}\n\nRetrieved context:\n{$context}");

        return $this->parseScore($response);
    }

    protected function parseScore(string $text): float
    {
        return ScoreParser::parse($text, min: 0.0, max: 1.0, default: 0.0);
    }
}
