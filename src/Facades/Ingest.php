<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Facades;

use Illuminate\Support\Facades\Facade;
use Moneo\LaravelRag\Pipeline\IngestPipeline;

/**
 * @method static IngestPipeline file(string $path)
 * @method static IngestPipeline text(string $content)
 *
 * @see \Moneo\LaravelRag\Pipeline\IngestPipeline
 */
class Ingest extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rag.ingest';
    }
}
