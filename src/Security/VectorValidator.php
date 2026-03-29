<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Security;

class VectorValidator
{
    /**
     * Validate an embedding vector before storage.
     *
     * Checks: correct dimensions, all values are finite floats, no NaN/INF.
     *
     * @param  array<int, mixed>  $vector  The vector to validate
     * @param  int  $expectedDimensions  The expected number of dimensions
     *
     * @throws InvalidVectorException
     */
    public static function validate(array $vector, int $expectedDimensions): void
    {
        $actualDimensions = count($vector);

        if ($actualDimensions !== $expectedDimensions) {
            throw new InvalidVectorException(
                "Vector dimension mismatch: expected {$expectedDimensions}, got {$actualDimensions}."
            );
        }

        if ($actualDimensions === 0) {
            throw new InvalidVectorException('Vector must not be empty.');
        }

        foreach ($vector as $index => $value) {
            if (! is_float($value) && ! is_int($value)) {
                throw new InvalidVectorException(
                    "Vector element at index {$index} is not a number: got ".gettype($value).'.'
                );
            }

            $floatValue = (float) $value;

            if (is_nan($floatValue)) {
                throw new InvalidVectorException(
                    "Vector element at index {$index} is NaN."
                );
            }

            if (is_infinite($floatValue)) {
                throw new InvalidVectorException(
                    "Vector element at index {$index} is infinite."
                );
            }
        }
    }
}
