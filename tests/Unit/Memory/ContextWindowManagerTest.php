<?php

declare(strict_types=1);

use Moneo\LaravelRag\Memory\ContextWindowManager;
use Moneo\LaravelRag\Memory\ThreadMessage;

test('formatMessages joins with role prefix', function () {
    $manager = new ContextWindowManager;
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('formatMessages');
    $method->setAccessible(true);

    $msg1 = new ThreadMessage;
    $msg1->forceFill(['role' => 'user', 'content' => 'Hello']);

    $msg2 = new ThreadMessage;
    $msg2->forceFill(['role' => 'assistant', 'content' => 'Hi there']);

    $result = $method->invoke($manager, collect([$msg1, $msg2]));

    expect($result)->toContain('User: Hello')
        ->and($result)->toContain('Assistant: Hi there');
});

test('formatMessages handles empty collection', function () {
    $manager = new ContextWindowManager;
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('formatMessages');
    $method->setAccessible(true);

    $result = $method->invoke($manager, collect());

    expect($result)->toBe('');
});

test('constructor reads config values', function () {
    config(['rag.memory.max_tokens' => 8000, 'rag.memory.summary_threshold' => 0.9]);

    $manager = new ContextWindowManager;
    $reflection = new ReflectionClass($manager);

    $maxTokens = $reflection->getProperty('maxTokens');
    $maxTokens->setAccessible(true);

    $threshold = $reflection->getProperty('summaryThreshold');
    $threshold->setAccessible(true);

    expect($maxTokens->getValue($manager))->toBe(8000)
        ->and($threshold->getValue($manager))->toBe(0.9);
});
