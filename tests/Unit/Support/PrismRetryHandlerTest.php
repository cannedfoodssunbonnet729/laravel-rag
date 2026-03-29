<?php

declare(strict_types=1);

use Moneo\LaravelRag\Exceptions\EmbeddingRateLimitException;
use Moneo\LaravelRag\Exceptions\EmbeddingServiceException;
use Moneo\LaravelRag\Exceptions\GenerationException;
use Moneo\LaravelRag\Support\PrismRetryHandler;

test('retry succeeds after transient failures', function () {
    $handler = Mockery::mock(PrismRetryHandler::class)->makePartial();
    $handler->shouldAllowMockingProtectedMethods();
    $handler->shouldReceive('sleep')->andReturn(); // Skip actual delay

    $callCount = 0;

    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('retry');
    $method->setAccessible(true);

    $result = $method->invoke($handler, function () use (&$callCount) {
        $callCount++;
        if ($callCount < 3) {
            throw new \RuntimeException('server error', 500);
        }

        return 'success';
    }, 'embedding');

    expect($result)->toBe('success')
        ->and($callCount)->toBe(3);
});

test('retry throws after max attempts exceeded', function () {
    $handler = Mockery::mock(PrismRetryHandler::class)->makePartial();
    $handler->shouldAllowMockingProtectedMethods();
    $handler->shouldReceive('sleep')->andReturn();

    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('retry');
    $method->setAccessible(true);

    $method->invoke($handler, function () {
        throw new \RuntimeException('always fails', 500);
    }, 'embedding');
})->throws(EmbeddingServiceException::class);

test('non-retryable errors throw immediately', function () {
    $handler = Mockery::mock(PrismRetryHandler::class)->makePartial();
    $handler->shouldAllowMockingProtectedMethods();
    $handler->shouldReceive('sleep')->never();

    $callCount = 0;

    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('retry');
    $method->setAccessible(true);

    try {
        $method->invoke($handler, function () use (&$callCount) {
            $callCount++;
            throw new \RuntimeException('bad request', 400);
        }, 'embedding');
    } catch (EmbeddingServiceException) {
        // Expected
    }

    expect($callCount)->toBe(1); // Only called once, no retries
});

test('rate limit errors are retried', function () {
    $handler = Mockery::mock(PrismRetryHandler::class)->makePartial();
    $handler->shouldAllowMockingProtectedMethods();
    $handler->shouldReceive('sleep')->andReturn();

    $callCount = 0;

    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('retry');
    $method->setAccessible(true);

    $result = $method->invoke($handler, function () use (&$callCount) {
        $callCount++;
        if ($callCount === 1) {
            throw new \RuntimeException('Rate limit exceeded', 429);
        }

        return 'success';
    }, 'embedding');

    expect($result)->toBe('success')
        ->and($callCount)->toBe(2);
});

test('generation errors use GenerationException', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('classify');
    $method->setAccessible(true);

    $classified = $method->invoke($handler, new \RuntimeException('error', 400), 'generation');

    expect($classified)->toBeInstanceOf(GenerationException::class);
});

test('calculateDelay increases exponentially', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('calculateDelay');
    $method->setAccessible(true);

    $delays = [];
    for ($i = 0; $i < 3; $i++) {
        $delays[] = $method->invoke($handler, $i);
    }

    // Each delay should be roughly double the previous (within jitter bounds)
    expect($delays[1])->toBeGreaterThan($delays[0])
        ->and($delays[2])->toBeGreaterThan($delays[1]);
});
