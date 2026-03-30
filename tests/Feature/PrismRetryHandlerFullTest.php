<?php

declare(strict_types=1);

use Moneo\LaravelRag\Exceptions\DimensionMismatchException;
use Moneo\LaravelRag\Exceptions\EmbeddingServiceException;
use Moneo\LaravelRag\Exceptions\GenerationException;
use Moneo\LaravelRag\Support\PrismRetryHandler;

test('embed method calls retry with embedding operation', function () {
    $handler = Mockery::mock(PrismRetryHandler::class)->makePartial();
    $handler->shouldAllowMockingProtectedMethods();
    $handler->shouldReceive('sleep')->andReturn();

    $callCount = 0;
    $ref = new ReflectionClass($handler);
    $method = $ref->getMethod('retry');
    $method->setAccessible(true);

    $result = $method->invoke($handler, function () use (&$callCount) {
        $callCount++;
        if ($callCount === 1) {
            throw new \RuntimeException('Connection timed out');
        }
        return [0.1, 0.2];
    }, 'embedding');

    expect($result)->toBe([0.1, 0.2])
        ->and($callCount)->toBe(2);
});

test('generate method classifies as GenerationException', function () {
    $handler = new PrismRetryHandler;
    $ref = new ReflectionClass($handler);
    $method = $ref->getMethod('classify');
    $method->setAccessible(true);

    $e = $method->invoke($handler, new \RuntimeException('Bad request', 400), 'generation');
    expect($e)->toBeInstanceOf(GenerationException::class);
});

test('embed classify preserves context', function () {
    $handler = new PrismRetryHandler;
    $ref = new ReflectionClass($handler);
    $method = $ref->getMethod('classify');
    $method->setAccessible(true);

    $e = $method->invoke($handler, new \RuntimeException('Server error', 503), 'embedding');
    expect($e)->toBeInstanceOf(EmbeddingServiceException::class)
        ->and($e->getContext()['operation'])->toBe('embedding')
        ->and($e->getContext()['status_code'])->toBe(503);
});

test('sleep method is called during retry', function () {
    $handler = Mockery::mock(PrismRetryHandler::class)->makePartial();
    $handler->shouldAllowMockingProtectedMethods();
    $handler->shouldReceive('sleep')->twice(); // 2 retries = 2 sleeps

    $ref = new ReflectionClass($handler);
    $method = $ref->getMethod('retry');
    $method->setAccessible(true);

    $calls = 0;
    $method->invoke($handler, function () use (&$calls) {
        $calls++;
        if ($calls <= 2) {
            throw new \RuntimeException('timeout', 500);
        }
        return 'ok';
    }, 'embedding');
});

test('calculateDelay with jitter stays within bounds', function () {
    $handler = new PrismRetryHandler;
    $ref = new ReflectionClass($handler);
    $method = $ref->getMethod('calculateDelay');
    $method->setAccessible(true);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $delay = $method->invoke($handler, $attempt);
        $baseDelay = 200 * (2 ** $attempt);
        $maxJitter = (int) ($baseDelay * 0.3);

        expect($delay)->toBeGreaterThanOrEqual($baseDelay)
            ->and($delay)->toBeLessThanOrEqual($baseDelay + $maxJitter);
    }
});
