<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Filament\Resources;

use Illuminate\Database\Eloquent\Model;

/**
 * Simple Eloquent model for the rag_embedding_cache table.
 *
 * @internal Used only by EmbeddingResource for Filament admin.
 */
class EmbeddingCacheModel extends Model
{
    protected $table = 'rag_embedding_cache';

    protected $guarded = [];
}
