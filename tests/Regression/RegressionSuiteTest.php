<?php

declare(strict_types=1);

/**
 * Regression test suite.
 *
 * These tests load fixtures with known-good question/answer pairs
 * and validate that the eval framework can process them.
 * Actual LLM-based regression runs happen in nightly CI with real API keys.
 *
 * @group regression
 * @group nightly
 */

test('regression fixture files are valid JSON', function () {
    $fixturesDir = __DIR__.'/fixtures';
    $files = glob("{$fixturesDir}/*.json");

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $content = file_get_contents($file);
        $decoded = json_decode($content, true);

        expect(json_last_error())->toBe(JSON_ERROR_NONE, "Invalid JSON in {$file}");
        expect($decoded)->toBeArray();
    }
});

test('regression fixtures have required structure', function () {
    $fixturesDir = __DIR__.'/fixtures';
    $files = glob("{$fixturesDir}/*.json");

    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        $cases = $data['cases'] ?? $data;

        foreach ($cases as $index => $case) {
            expect($case)->toHaveKeys(['question', 'expected'], "Case #{$index} in {$file} missing required keys");
            expect($case['question'])->toBeString()->not->toBeEmpty();
            expect($case['expected'])->toBeString()->not->toBeEmpty();
        }
    }
});

test('basic_rag fixture has at least 5 cases', function () {
    $data = json_decode(file_get_contents(__DIR__.'/fixtures/basic_rag.json'), true);

    expect($data['cases'])->toHaveCount(5);
});
