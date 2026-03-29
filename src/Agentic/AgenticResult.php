<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Agentic;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Arrayable<string, mixed>
 */
class AgenticResult implements Arrayable
{
    /**
     * @param  string  $answer  The final generated answer
     * @param  array<int, array{query: string, chunks_retrieved: int, sufficient: bool}>  $steps  Each retrieval step
     * @param  int  $totalChunksRetrieved  Total chunks across all steps
     * @param  Collection<int, array{id: string, score: float, metadata: array, content: string}>  $allChunks  All retrieved chunks
     * @param  float  $totalTimeMs  Total time in milliseconds
     */
    public function __construct(
        public readonly string $answer,
        public readonly array $steps,
        public readonly int $totalChunksRetrieved,
        public readonly Collection $allChunks,
        public readonly float $totalTimeMs,
    ) {}

    /**
     * How many retrieval steps were taken.
     */
    public function stepCount(): int
    {
        return count($this->steps);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'answer' => $this->answer,
            'steps' => $this->steps,
            'total_chunks_retrieved' => $this->totalChunksRetrieved,
            'step_count' => $this->stepCount(),
            'total_time_ms' => round($this->totalTimeMs, 2),
        ];
    }
}
