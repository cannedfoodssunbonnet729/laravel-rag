<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Filament\Resources\EmbeddingResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Moneo\LaravelRag\Filament\Resources\EmbeddingResource;

class ListEmbeddings extends ListRecords
{
    protected static string $resource = EmbeddingResource::class;
}
