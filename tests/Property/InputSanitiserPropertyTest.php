<?php

declare(strict_types=1);

use Moneo\LaravelRag\Security\InputSanitiser;

$iterations = (int) (getenv('RAG_ERIS_ITERATIONS') ?: 1000);

test('idempotency: sanitise(sanitise(x)) === sanitise(x) for all strings', function () use ($iterations) {
    for ($i = 0; $i < $iterations; $i++) {
        $input = bin2hex(random_bytes(random_int(1, 500)));

        $once = InputSanitiser::clean($input);
        $twice = InputSanitiser::clean($once);

        expect($twice)->toBe($once,
            "Sanitiser is not idempotent for input length ".strlen($input)
        );
    }
});

test('sanitiser never adds content: len(sanitise(x)) <= len(x)', function () use ($iterations) {
    for ($i = 0; $i < $iterations; $i++) {
        $input = bin2hex(random_bytes(random_int(1, 500)));

        $cleaned = InputSanitiser::clean($input);

        expect(mb_strlen($cleaned))->toBeLessThanOrEqual(mb_strlen($input),
            "Sanitiser added content: input=".mb_strlen($input)." output=".mb_strlen($cleaned)
        );
    }
});

test('sanitiser handles all possible byte sequences without crashing', function () use ($iterations) {
    for ($i = 0; $i < $iterations; $i++) {
        // Generate truly random bytes including null bytes, RTL markers, etc.
        $input = random_bytes(random_int(1, 200));

        // Must not throw
        $result = InputSanitiser::clean($input);

        expect($result)->toBeString();
    }
});

test('containsInjection is consistent with clean', function () use ($iterations) {
    for ($i = 0; $i < min($iterations, 500); $i++) {
        $input = bin2hex(random_bytes(random_int(1, 200)));

        $hasInjection = InputSanitiser::containsInjection($input);
        $cleaned = InputSanitiser::clean($input);

        if ($hasInjection) {
            // If injection detected, cleaning should change the input
            expect($cleaned)->not->toBe($input,
                "containsInjection=true but clean() did not change the input"
            );
        }
    }
});
