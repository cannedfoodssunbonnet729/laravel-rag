<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Evals\Metrics;

use Moneo\LaravelRag\Support\PrismRetryHandler;
use Moneo\LaravelRag\Support\ScoreParser;

class FaithfulnessMetric implements MetricContract
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'faithfulness';
    }

    /**
     * Evaluate whether the answer is faithful to the retrieved context.
     *
     * A score of 1.0 means the answer only contains claims supported by the context.
     * A score of 0.0 means the answer contains fabricated information.
     */
    public function evaluate(string $question, string $answer, string $expected, string $context): float
    {
        $provider = config('rag.llm.provider');
        $model = config('rag.llm.model');

        $response = app(PrismRetryHandler::class)->generate($provider, $model, 'You are an evaluation judge. Assess the FAITHFULNESS of the answer to the provided context.

Faithfulness measures whether EVERY claim in the answer can be inferred from the context.

Score on a scale of 0.0 to 1.0:
- 1.0: Every claim in the answer is directly supported by the context
- 0.5: Some claims are supported, some are not
- 0.0: The answer contains mostly fabricated information not in the context

Respond with ONLY a decimal number between 0.0 and 1.0.', "Question: {$question}\n\nContext:\n{$context}\n\nAnswer: {$answer}");

        return $this->parseScore($response);
    }

    protected function parseScore(string $text): float
    {
        return ScoreParser::parse($text, min: 0.0, max: 1.0, default: 0.0);
    }
}
