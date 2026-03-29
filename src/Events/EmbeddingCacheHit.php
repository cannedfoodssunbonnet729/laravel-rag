<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class EmbeddingCacheHit
{
    use Dispatchable;

    /**
     * @param  Model|null  $model  The model that was embedded (null for pipeline ingest)
     * @param  string  $sourceText  The text that was cached
     */
    public function __construct(
        public readonly ?Model $model,
        public readonly string $sourceText,
    ) {}
}
