<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Support;

/**
 * Robust parser for LLM-generated numeric scores.
 *
 * LLMs don't always follow instructions to "respond with only a number".
 * This parser extracts the first numeric value from any text response.
 */
class ScoreParser
{
    /**
     * Parse a numeric score from LLM text output.
     *
     * Extracts the first decimal or integer from the text, clamps it to [min, max].
     * Returns $default if no number is found.
     *
     * @param  string  $text  The raw LLM output
     * @param  float  $min  Minimum allowed score
     * @param  float  $max  Maximum allowed score
     * @param  float  $default  Default if no number found
     * @return float  The parsed and clamped score
     */
    public static function parse(string $text, float $min = 0.0, float $max = 10.0, float $default = 0.0): float
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return $default;
        }

        // Try to extract the first numeric value (integer or decimal)
        if (preg_match('/(\d+(?:\.\d+)?)/', $trimmed, $matches)) {
            $score = (float) $matches[1];

            return max($min, min($max, $score));
        }

        return $default;
    }
}
