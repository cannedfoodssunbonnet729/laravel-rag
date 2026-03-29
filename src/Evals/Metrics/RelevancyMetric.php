<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Evals\Metrics;

use Moneo\LaravelRag\Support\PrismRetryHandler;
use Moneo\LaravelRag\Support\ScoreParser;

class RelevancyMetric implements MetricContract
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'relevancy';
    }

    /**
     * Evaluate whether the answer is relevant to the question.
     *
     * A score of 1.0 means the answer directly addresses the question.
     * A score of 0.0 means the answer is completely off-topic.
     */
    public function evaluate(string $question, string $answer, string $expected, string $context): float
    {
        $provider = config('rag.llm.provider');
        $model = config('rag.llm.model');

        $response = app(PrismRetryHandler::class)->generate($provider, $model, 'You are an evaluation judge. Assess the ANSWER RELEVANCY — how well the answer addresses the question.

Score on a scale of 0.0 to 1.0:
- 1.0: The answer directly and completely addresses the question
- 0.5: The answer partially addresses the question
- 0.0: The answer is completely irrelevant to the question

Respond with ONLY a decimal number between 0.0 and 1.0.', "Question: {$question}\n\nAnswer: {$answer}\n\nExpected answer: {$expected}");

        return $this->parseScore($response);
    }

    protected function parseScore(string $text): float
    {
        return ScoreParser::parse($text, min: 0.0, max: 1.0, default: 0.0);
    }
}
