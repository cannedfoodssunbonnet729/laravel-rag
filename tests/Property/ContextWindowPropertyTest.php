<?php

declare(strict_types=1);

/**
 * Property-based tests for ContextWindowManager.
 *
 * Note: These test the formatting/splitting logic, not the LLM summarisation
 * (which requires mocking Prism and is covered in unit/chaos tests).
 */

use Moneo\LaravelRag\Memory\ContextWindowManager;
use Moneo\LaravelRag\Memory\ThreadMessage;

$iterations = (int) (getenv('RAG_ERIS_ITERATIONS') ?: 1000);

test('formatMessages preserves all message content for any number of messages', function () use ($iterations) {
    $manager = new ContextWindowManager;
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('formatMessages');
    $method->setAccessible(true);

    for ($i = 0; $i < min($iterations, 500); $i++) {
        $count = random_int(0, 20);
        $messages = collect();

        for ($j = 0; $j < $count; $j++) {
            $msg = Mockery::mock(ThreadMessage::class);
            $msg->role = ['user', 'assistant'][random_int(0, 1)];
            $msg->content = bin2hex(random_bytes(random_int(1, 100)));
            $messages->push($msg);
        }

        $formatted = $method->invoke($manager, $messages);

        // Every message's content must appear in the formatted output
        foreach ($messages as $msg) {
            if (! empty($msg->content)) {
                expect($formatted)->toContain($msg->content);
            }
        }
    }
});

test('formatMessages with empty collection always returns empty string', function () {
    $manager = new ContextWindowManager;
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('formatMessages');
    $method->setAccessible(true);

    $result = $method->invoke($manager, collect());

    expect($result)->toBe('');
});
