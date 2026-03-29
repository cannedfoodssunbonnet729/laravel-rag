<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests\Benchmarks;

use Moneo\LaravelRag\Security\VectorValidator;
use PhpBench\Attributes as Bench;

class BenchmarkVectorValidation
{
    /** @var array<int, float> */
    private array $vector1536;

    /** @var array<int, float> */
    private array $vector3072;

    public function __construct()
    {
        $this->vector1536 = array_fill(0, 1536, 0.1);
        $this->vector3072 = array_fill(0, 3072, 0.1);
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function bench1536Dims(): void
    {
        VectorValidator::validate($this->vector1536, 1536);
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function bench3072Dims(): void
    {
        VectorValidator::validate($this->vector3072, 3072);
    }
}
