<?php

declare(strict_types=1);

use Moneo\LaravelRag\Exceptions\CacheTableMissingException;
use Moneo\LaravelRag\Exceptions\DeadlockException;
use Moneo\LaravelRag\Exceptions\DimensionMismatchException;
use Moneo\LaravelRag\Exceptions\EmbeddingException;
use Moneo\LaravelRag\Exceptions\EmbeddingRateLimitException;
use Moneo\LaravelRag\Exceptions\EmbeddingResponseException;
use Moneo\LaravelRag\Exceptions\EmbeddingServiceException;
use Moneo\LaravelRag\Exceptions\EmbeddingTimeoutException;
use Moneo\LaravelRag\Exceptions\GenerationException;
use Moneo\LaravelRag\Exceptions\RagException;
use Moneo\LaravelRag\Exceptions\VectorStoreException;
use Moneo\LaravelRag\Exceptions\VectorStoreLockException;

test('RagException withContext stores and retrieves context', function () {
    $e = new class('test') extends RagException {};
    $e->withContext(['key' => 'value', 'number' => 42]);

    expect($e->getContext())->toBe(['key' => 'value', 'number' => 42]);
});

test('withContext merges context', function () {
    $e = new class('test') extends RagException {};
    $e->withContext(['a' => 1]);
    $e->withContext(['b' => 2]);

    expect($e->getContext())->toBe(['a' => 1, 'b' => 2]);
});

test('getContext returns empty array by default', function () {
    $e = new class('test') extends RagException {};

    expect($e->getContext())->toBe([]);
});

test('withContext returns self for fluent chaining', function () {
    $e = new class('test') extends RagException {};
    $result = $e->withContext(['key' => 'val']);

    expect($result)->toBe($e);
});

// Inheritance hierarchy
test('EmbeddingException extends RagException', function () {
    expect(new EmbeddingException('test'))->toBeInstanceOf(RagException::class);
});

test('EmbeddingRateLimitException extends EmbeddingException', function () {
    expect(new EmbeddingRateLimitException('test'))->toBeInstanceOf(EmbeddingException::class);
});

test('EmbeddingServiceException extends EmbeddingException', function () {
    expect(new EmbeddingServiceException('test'))->toBeInstanceOf(EmbeddingException::class);
});

test('EmbeddingTimeoutException extends EmbeddingException', function () {
    expect(new EmbeddingTimeoutException('test'))->toBeInstanceOf(EmbeddingException::class);
});

test('EmbeddingResponseException extends EmbeddingException', function () {
    expect(new EmbeddingResponseException('test'))->toBeInstanceOf(EmbeddingException::class);
});

test('DimensionMismatchException extends EmbeddingException', function () {
    expect(new DimensionMismatchException('test'))->toBeInstanceOf(EmbeddingException::class);
});

test('VectorStoreException extends RagException', function () {
    expect(new VectorStoreException('test'))->toBeInstanceOf(RagException::class);
});

test('DeadlockException extends VectorStoreException', function () {
    expect(new DeadlockException('test'))->toBeInstanceOf(VectorStoreException::class);
});

test('VectorStoreLockException extends VectorStoreException', function () {
    expect(new VectorStoreLockException('test'))->toBeInstanceOf(VectorStoreException::class);
});

test('GenerationException extends RagException', function () {
    expect(new GenerationException('test'))->toBeInstanceOf(RagException::class);
});

test('CacheTableMissingException has migration hint in message', function () {
    $e = new CacheTableMissingException;

    expect($e->getMessage())->toContain('rag_embedding_cache')
        ->and($e->getMessage())->toContain('php artisan');
});

test('MissingApiKeyException includes provider name and instructions', function () {
    $e = new \Moneo\LaravelRag\Exceptions\MissingApiKeyException('openai');

    expect($e->getMessage())->toContain('openai')
        ->and($e->getMessage())->toContain('OPENAI_API_KEY')
        ->and($e->getMessage())->toContain('.env');
});

test('MissingApiKeyException extends RagException', function () {
    expect(new \Moneo\LaravelRag\Exceptions\MissingApiKeyException)->toBeInstanceOf(RagException::class);
});
