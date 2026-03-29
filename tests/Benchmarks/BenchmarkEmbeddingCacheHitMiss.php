<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests\Benchmarks;

use Moneo\LaravelRag\Security\CacheIntegrityGuard;
use PhpBench\Attributes as Bench;

class BenchmarkEmbeddingCacheHitMiss
{
    private string $appKey = 'benchmark-key-12345';

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function benchHmacHashGeneration(): void
    {
        CacheIntegrityGuard::signedHash('This is a test embedding text for benchmarking cache key generation', $this->appKey);
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function benchHmacVerification(): void
    {
        $text = 'This is a test embedding text for benchmarking';
        $hash = CacheIntegrityGuard::signedHash($text, $this->appKey);
        CacheIntegrityGuard::verify($hash, $text, $this->appKey);
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    public function benchVectorValidation(): void
    {
        $vector = array_fill(0, 1536, 0.1);
        CacheIntegrityGuard::validateCachedVector($vector);
    }
}
