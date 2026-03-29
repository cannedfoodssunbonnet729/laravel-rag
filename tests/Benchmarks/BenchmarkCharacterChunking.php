<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests\Benchmarks;

use Moneo\LaravelRag\Chunking\Strategies\CharacterChunker;
use PhpBench\Attributes as Bench;

class BenchmarkCharacterChunking
{
    private CharacterChunker $chunker;

    private string $text10k;

    private string $text100k;

    public function __construct()
    {
        $this->chunker = new CharacterChunker;
        $this->text10k = str_repeat('This is a sample sentence for benchmarking purposes. ', 200);
        $this->text100k = str_repeat('This is a sample sentence for benchmarking purposes. ', 2000);
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function bench10kChars(): void
    {
        $this->chunker->chunk($this->text10k, ['size' => 500, 'overlap' => 50]);
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function bench100kChars(): void
    {
        $this->chunker->chunk($this->text100k, ['size' => 500, 'overlap' => 50]);
    }
}
