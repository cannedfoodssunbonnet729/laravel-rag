<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\InvalidVectorException;
use Moneo\LaravelRag\Security\VectorValidator;

test('accepts valid vector with correct dimensions', function () {
    VectorValidator::validate([0.1, 0.2, 0.3], 3);
})->throwsNoExceptions();

test('rejects vector with wrong dimensions', function () {
    VectorValidator::validate([0.1, 0.2], 3);
})->throws(InvalidVectorException::class, 'dimension mismatch');

test('rejects empty vector', function () {
    VectorValidator::validate([], 0);
})->throws(InvalidVectorException::class, 'must not be empty');

test('rejects vector with NaN', function () {
    VectorValidator::validate([0.1, NAN, 0.3], 3);
})->throws(InvalidVectorException::class, 'NaN');

test('rejects vector with positive infinity', function () {
    VectorValidator::validate([0.1, INF, 0.3], 3);
})->throws(InvalidVectorException::class, 'infinite');

test('rejects vector with negative infinity', function () {
    VectorValidator::validate([0.1, -INF, 0.3], 3);
})->throws(InvalidVectorException::class, 'infinite');

test('rejects vector with string element', function () {
    VectorValidator::validate([0.1, 'bad', 0.3], 3);
})->throws(InvalidVectorException::class, 'not a number');

test('rejects vector with null element', function () {
    VectorValidator::validate([0.1, null, 0.3], 3);
})->throws(InvalidVectorException::class, 'not a number');

test('accepts vector with integer elements', function () {
    VectorValidator::validate([1, 2, 3], 3);
})->throwsNoExceptions();

test('accepts high-dimensional vector (3072)', function () {
    $vector = array_fill(0, 3072, 0.1);
    VectorValidator::validate($vector, 3072);
})->throwsNoExceptions();

test('accepts negative float values', function () {
    VectorValidator::validate([-0.5, -1.0, 0.0], 3);
})->throwsNoExceptions();

test('accepts zero vector', function () {
    VectorValidator::validate([0.0, 0.0, 0.0], 3);
})->throwsNoExceptions();
