<?php

declare(strict_types=1);

use Moneo\LaravelRag\Concerns\AutoEmbeds;

test('getEmbedSource returns default', function () {
    $model = new class {
        use AutoEmbeds;
    };

    expect($model->getEmbedSource())->toBe('content');
});

test('getEmbedSource returns custom value', function () {
    $model = new class {
        use AutoEmbeds;

        protected string $embedSource = 'body';
    };

    expect($model->getEmbedSource())->toBe('body');
});

test('getEmbedSource returns array', function () {
    $model = new class {
        use AutoEmbeds;

        protected array $embedSource = ['title', 'body'];
    };

    expect($model->getEmbedSource())->toBe(['title', 'body']);
});

test('getVectorColumnName returns default', function () {
    $model = new class {
        use AutoEmbeds;
    };

    expect($model->getVectorColumnName())->toBe('embedding');
});

test('getEmbedAsync returns default false', function () {
    $model = new class {
        use AutoEmbeds;
    };

    expect($model->getEmbedAsync())->toBeFalse();
});

test('getEmbedAsync returns custom value', function () {
    $model = new class {
        use AutoEmbeds;

        protected bool $embedAsync = true;
    };

    expect($model->getEmbedAsync())->toBeTrue();
});

test('getEmbedCacheEnabled returns default true', function () {
    $model = new class {
        use AutoEmbeds;
    };

    expect($model->getEmbedCacheEnabled())->toBeTrue();
});

test('getEmbedSourceText concatenates columns', function () {
    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use AutoEmbeds;

        protected $fillable = ['title', 'body'];

        protected array $embedSource = ['title', 'body'];
    };

    $model->title = 'Hello';
    $model->body = 'World';

    expect($model->getEmbedSourceText())->toBe("Hello\n\nWorld");
});

test('getEmbedSourceText skips empty columns', function () {
    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use AutoEmbeds;

        protected $fillable = ['title', 'body'];

        protected array $embedSource = ['title', 'body'];
    };

    $model->title = 'Hello';
    $model->body = '';

    expect($model->getEmbedSourceText())->toBe('Hello');
});

test('shouldGenerateEmbedding returns true when source is dirty', function () {
    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use AutoEmbeds;

        protected $fillable = ['content'];
    };

    $model->content = 'new value';

    expect($model->shouldGenerateEmbedding())->toBeTrue();
});

test('shouldGenerateEmbedding returns false when clean', function () {
    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use AutoEmbeds;

        protected $fillable = ['content'];
    };

    $model->syncOriginal(); // Mark as clean

    expect($model->shouldGenerateEmbedding())->toBeFalse();
});
