<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Filament\Resources\DocumentResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Moneo\LaravelRag\Filament\Resources\DocumentResource;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;
}
