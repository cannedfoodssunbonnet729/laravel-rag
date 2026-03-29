<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

class RagPlugin implements Plugin
{
    public static function make(): static
    {
        return new static;
    }

    public function getId(): string
    {
        return 'laravel-rag';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                Resources\DocumentResource::class,
                Resources\EmbeddingResource::class,
            ])
            ->pages([
                Pages\RagTestPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
