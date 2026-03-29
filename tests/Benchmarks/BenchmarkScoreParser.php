<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests\Benchmarks;

use Moneo\LaravelRag\Support\ScoreParser;
use PhpBench\Attributes as Bench;

class BenchmarkScoreParser
{
    #[Bench\Revs(10000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function benchPlainNumber(): void
    {
        ScoreParser::parse('0.85', 0.0, 1.0, 0.0);
    }

    #[Bench\Revs(10000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function benchTextWithNumber(): void
    {
        ScoreParser::parse('The relevance score is 7.5 out of 10 based on the context', 0.0, 10.0, 0.0);
    }

    #[Bench\Revs(10000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function benchNoNumber(): void
    {
        ScoreParser::parse('eight out of ten', 0.0, 10.0, 5.0);
    }
}
