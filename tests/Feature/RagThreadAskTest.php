<?php

declare(strict_types=1);

use Moneo\LaravelRag\Facades\Rag;
use Moneo\LaravelRag\Memory\ContextWindowManager;
use Moneo\LaravelRag\Memory\RagThread;
use Moneo\LaravelRag\Pipeline\RagPipeline;
use Moneo\LaravelRag\Pipeline\RagResult;

test('RagThread ask stores user and assistant messages', function () {
    $result = new RagResult(
        answer: 'Laravel is a PHP framework.',
        chunks: collect(),
        question: 'What is Laravel?',
        retrievalTimeMs: 10,
        generationTimeMs: 20,
    );

    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('from')->andReturn($pipeline);
    $pipeline->shouldReceive('systemPrompt')->andReturn($pipeline);
    $pipeline->shouldReceive('ask')->andReturn($result);

    Rag::shouldReceive('from')->andReturn($pipeline);

    // Mock context window manager
    $cwm = Mockery::mock(ContextWindowManager::class);
    $cwm->shouldReceive('buildContext')->andReturn('');
    app()->instance(ContextWindowManager::class, $cwm);

    $thread = RagThread::create(['model' => 'App\\Models\\Document']);
    $ragResult = $thread->ask('What is Laravel?');

    expect($ragResult->answer)->toBe('Laravel is a PHP framework.')
        ->and($thread->messages()->count())->toBe(2)
        ->and($thread->messages()->first()->role)->toBe('user')
        ->and($thread->messages()->first()->content)->toBe('What is Laravel?')
        ->and($thread->messages()->get()->last()->role)->toBe('assistant');
});

test('RagThread ask passes conversation context to pipeline', function () {
    $result = new RagResult(
        answer: 'Second answer.',
        chunks: collect(),
        question: 'Follow up?',
        retrievalTimeMs: 10,
        generationTimeMs: 20,
    );

    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('from')->andReturn($pipeline);
    $pipeline->shouldReceive('systemPrompt')
        ->withArgs(fn ($prompt) => str_contains($prompt, 'conversation history'))
        ->once()
        ->andReturn($pipeline);
    $pipeline->shouldReceive('ask')->andReturn($result);

    Rag::shouldReceive('from')->andReturn($pipeline);

    $cwm = Mockery::mock(ContextWindowManager::class);
    $cwm->shouldReceive('buildContext')->andReturn('User: Previous question\n\nAssistant: Previous answer');
    app()->instance(ContextWindowManager::class, $cwm);

    $thread = RagThread::create(['model' => 'App\\Models\\Document']);
    $thread->ask('Follow up?');
});
