<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Facades;

use Illuminate\Support\Facades\Facade;
use Moneo\LaravelRag\Pipeline\RagPipeline;

/**
 * @method static RagPipeline from(string $modelClass)
 *
 * @see \Moneo\LaravelRag\Pipeline\RagPipeline
 */
class Rag extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rag.pipeline';
    }
}
