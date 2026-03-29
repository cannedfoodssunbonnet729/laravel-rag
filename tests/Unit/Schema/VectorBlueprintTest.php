<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Moneo\LaravelRag\Schema\VectorBlueprint;

test('vector macro is registered on Blueprint', function () {
    VectorBlueprint::register();

    expect(Blueprint::hasMacro('vector'))->toBeTrue();
});

test('vectorIndex macro is registered on Blueprint', function () {
    VectorBlueprint::register();

    expect(Blueprint::hasMacro('vectorIndex'))->toBeTrue();
});

test('fulltextIndex macro is registered on Blueprint', function () {
    VectorBlueprint::register();

    expect(Blueprint::hasMacro('fulltextIndex'))->toBeTrue();
});

test('register is idempotent', function () {
    VectorBlueprint::register();
    VectorBlueprint::register();

    // Should not throw — double registration is safe
    expect(Blueprint::hasMacro('vector'))->toBeTrue();
});
