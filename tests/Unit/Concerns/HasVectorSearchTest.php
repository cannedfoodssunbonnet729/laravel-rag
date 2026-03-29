<?php

declare(strict_types=1);

use Moneo\LaravelRag\Concerns\HasVectorSearch;
use Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract;

test('getVectorColumn returns default', function () {
    $model = new class {
        use HasVectorSearch;
    };

    expect($model->getVectorColumn())->toBe('embedding');
});

test('getVectorColumn returns custom value', function () {
    $model = new class {
        use HasVectorSearch;

        protected string $vectorColumn = 'custom_embedding';
    };

    expect($model->getVectorColumn())->toBe('custom_embedding');
});

test('getVectorDistance returns default', function () {
    $model = new class {
        use HasVectorSearch;
    };

    expect($model->getVectorDistance())->toBe('cosine');
});

test('getFulltextColumn returns default', function () {
    $model = new class {
        use HasVectorSearch;
    };

    expect($model->getFulltextColumn())->toBe('content');
});

test('getEmbeddingVector handles string format', function () {
    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use HasVectorSearch;

        protected $attributes = ['embedding' => '[0.1,0.2,0.3]'];
    };

    expect($model->getEmbeddingVector())->toBe([0.1, 0.2, 0.3]);
});

test('getEmbeddingVector handles array format', function () {
    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use HasVectorSearch;

        protected $casts = ['embedding' => 'array'];

        protected $attributes = ['embedding' => '[0.1,0.2]'];
    };

    // When cast to array, getAttribute returns array
    $model->embedding = [0.4, 0.5];

    expect($model->getEmbeddingVector())->toBe([0.4, 0.5]);
});

test('getEmbeddingVector returns empty for null', function () {
    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use HasVectorSearch;

        protected $attributes = ['embedding' => null];
    };

    expect($model->getEmbeddingVector())->toBe([]);
});
