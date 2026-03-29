<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests\Benchmarks;

use Moneo\LaravelRag\Chunking\Strategies\SentenceChunker;
use PhpBench\Attributes as Bench;

class BenchmarkSentenceChunking
{
    private SentenceChunker $chunker;

    private string $text10k;

    private string $text100k;

    public function __construct()
    {
        $this->chunker = new SentenceChunker;
        $this->text10k = str_repeat('This is sentence one. Here is another sentence. And a third one! Really? Yes. ', 130);
        $this->text100k = str_repeat('This is sentence one. Here is another sentence. And a third one! Really? Yes. ', 1300);
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function bench10kChars(): void
    {
        $this->chunker->chunk($this->text10k, ['size' => 500]);
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function bench100kChars(): void
    {
        $this->chunker->chunk($this->text100k, ['size' => 500]);
    }
}
