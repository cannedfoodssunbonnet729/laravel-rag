<?php

declare(strict_types=1);

use Moneo\LaravelRag\Exceptions\EmbeddingRateLimitException;
use Moneo\LaravelRag\Exceptions\EmbeddingServiceException;
use Moneo\LaravelRag\Exceptions\EmbeddingTimeoutException;
use Moneo\LaravelRag\Exceptions\EmbeddingResponseException;
use Moneo\LaravelRag\Support\PrismRetryHandler;

/**
 * Chaos tests for embedding API failures.
 *
 * @group chaos
 */

test('classifies 429 as EmbeddingRateLimitException', function () {
    $handler = Mockery::mock(PrismRetryHandler::class)->makePartial();
    $handler->shouldAllowMockingProtectedMethods();

    // Override sleep to not actually wait
    $handler->shouldReceive('sleep')->andReturn();

    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('classify');
    $method->setAccessible(true);

    $original = new \RuntimeException('Rate limit exceeded', 429);
    $classified = $method->invoke($handler, $original, 'embedding');

    expect($classified)->toBeInstanceOf(EmbeddingRateLimitException::class)
        ->and($classified->getCode())->toBe(429);
});

test('classifies 500 as EmbeddingServiceException', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('classify');
    $method->setAccessible(true);

    $original = new \RuntimeException('Internal server error', 500);
    $classified = $method->invoke($handler, $original, 'embedding');

    expect($classified)->toBeInstanceOf(EmbeddingServiceException::class)
        ->and($classified->getCode())->toBe(500);
});

test('classifies timeout errors as EmbeddingTimeoutException', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('classify');
    $method->setAccessible(true);

    $original = new \RuntimeException('Connection timed out', 0);
    $classified = $method->invoke($handler, $original, 'embedding');

    expect($classified)->toBeInstanceOf(EmbeddingTimeoutException::class);
});

test('classifies JSON errors as EmbeddingResponseException', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('classify');
    $method->setAccessible(true);

    $original = new \RuntimeException('Failed to decode JSON response', 0);
    $classified = $method->invoke($handler, $original, 'embedding');

    expect($classified)->toBeInstanceOf(EmbeddingResponseException::class);
});

test('server errors are retryable', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('isRetryable');
    $method->setAccessible(true);

    expect($method->invoke($handler, new \RuntimeException('error', 500)))->toBeTrue()
        ->and($method->invoke($handler, new \RuntimeException('error', 502)))->toBeTrue()
        ->and($method->invoke($handler, new \RuntimeException('error', 503)))->toBeTrue();
});

test('client errors are not retryable', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('isRetryable');
    $method->setAccessible(true);

    expect($method->invoke($handler, new \RuntimeException('bad request', 400)))->toBeFalse()
        ->and($method->invoke($handler, new \RuntimeException('unauthorized', 401)))->toBeFalse();
});

test('timeout errors are retryable', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('isRetryable');
    $method->setAccessible(true);

    expect($method->invoke($handler, new \RuntimeException('Connection timed out')))->toBeTrue()
        ->and($method->invoke($handler, new \RuntimeException('Connection timeout')))->toBeTrue();
});

test('calculateDelay uses exponential backoff', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('calculateDelay');
    $method->setAccessible(true);

    $delay0 = $method->invoke($handler, 0);
    $delay1 = $method->invoke($handler, 1);
    $delay2 = $method->invoke($handler, 2);

    // Each delay should roughly double (with jitter)
    expect($delay1)->toBeGreaterThan($delay0)
        ->and($delay2)->toBeGreaterThan($delay1);
});

test('RagException context is preserved', function () {
    $handler = new PrismRetryHandler;
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('classify');
    $method->setAccessible(true);

    $original = new \RuntimeException('timeout', 0);
    $classified = $method->invoke($handler, $original, 'embedding');

    expect($classified->getContext())->toHaveKey('operation')
        ->and($classified->getContext()['operation'])->toBe('embedding');
});
