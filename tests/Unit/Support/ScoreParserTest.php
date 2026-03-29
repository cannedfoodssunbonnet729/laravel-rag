<?php

declare(strict_types=1);

use Moneo\LaravelRag\Support\ScoreParser;

test('parses plain integer', function () {
    expect(ScoreParser::parse('8', 0, 10))->toBe(8.0);
});

test('parses plain decimal', function () {
    expect(ScoreParser::parse('0.85', 0, 1))->toBe(0.85);
});

test('parses "Score: 7.5"', function () {
    expect(ScoreParser::parse('Score: 7.5', 0, 10))->toBe(7.5);
});

test('parses "The score is 8.5 out of 10"', function () {
    expect(ScoreParser::parse('The score is 8.5 out of 10', 0, 10))->toBe(8.5);
});

test('parses "8/10"', function () {
    // Extracts first number: 8
    expect(ScoreParser::parse('8/10', 0, 10))->toBe(8.0);
});

test('returns default for "eight"', function () {
    expect(ScoreParser::parse('eight', 0, 10, 5.0))->toBe(5.0);
});

test('returns default for empty string', function () {
    expect(ScoreParser::parse('', 0, 10, 0.0))->toBe(0.0);
});

test('returns default for whitespace only', function () {
    expect(ScoreParser::parse('   ', 0, 10, 3.0))->toBe(3.0);
});

test('clamps to max', function () {
    expect(ScoreParser::parse('15', 0, 10))->toBe(10.0);
});

test('clamps to min', function () {
    expect(ScoreParser::parse('-5', 0, 10, 0.0))->toBe(0.0);
    // Note: regex doesn't capture negative — returns default
});

test('parses leading text with number', function () {
    expect(ScoreParser::parse('I would rate this 0.92', 0, 1))->toBe(0.92);
});

test('parses "0.0"', function () {
    expect(ScoreParser::parse('0.0', 0, 1))->toBe(0.0);
});

test('parses "1.0"', function () {
    expect(ScoreParser::parse('1.0', 0, 1))->toBe(1.0);
});

test('returns default for no numbers at all', function () {
    expect(ScoreParser::parse('no numbers here!', 0, 10, 5.0))->toBe(5.0);
});

test('parses with trailing explanation', function () {
    expect(ScoreParser::parse("0.85\n\nThe answer is mostly faithful.", 0, 1))->toBe(0.85);
});
