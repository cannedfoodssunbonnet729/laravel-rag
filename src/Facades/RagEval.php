<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Facades;

use Illuminate\Support\Facades\Facade;
use Moneo\LaravelRag\Evals\RagEval as RagEvalManager;

/**
 * @method static RagEvalManager suite()
 *
 * @see \Moneo\LaravelRag\Evals\RagEval
 */
class RagEval extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rag.eval';
    }
}
