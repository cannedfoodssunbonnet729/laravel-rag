<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Tests;

use Moneo\LaravelRag\RagServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            RagServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Rag' => \Moneo\LaravelRag\Facades\Rag::class,
            'Ingest' => \Moneo\LaravelRag\Facades\Ingest::class,
            'RagEval' => \Moneo\LaravelRag\Facades\RagEval::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('rag.vector_store', 'sqlite-vec');
        $app['config']->set('rag.embedding.driver', 'openai');
        $app['config']->set('rag.embedding.model', 'text-embedding-3-small');
        $app['config']->set('rag.embedding.dimensions', 1536);
        $app['config']->set('rag.embedding.cache', false);
        $app['config']->set('rag.llm.provider', 'openai');
        $app['config']->set('rag.llm.model', 'gpt-4o');
    }
}
