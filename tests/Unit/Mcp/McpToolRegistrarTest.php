<?php

declare(strict_types=1);

use Moneo\LaravelRag\Mcp\McpToolRegistrar;
use Moneo\LaravelRag\Mcp\RagMcpServer;
use Moneo\LaravelRag\Pipeline\RagPipeline;

test('as sets the tool name', function () {
    $server = new RagMcpServer;
    $registrar = new McpToolRegistrar($server, 'App\\Models\\Document');

    $result = $registrar->as('custom-name');

    expect($result)->toBe($registrar); // fluent
});

test('description sets the tool description', function () {
    $server = new RagMcpServer;
    $registrar = new McpToolRegistrar($server, 'App\\Models\\Document');

    $result = $registrar->description('Search company docs');

    expect($result)->toBe($registrar); // fluent
});

test('expose registers tool on server', function () {
    $server = new RagMcpServer;

    // Mock the Rag facade to return a pipeline
    $pipeline = Mockery::mock(RagPipeline::class);
    $pipeline->shouldReceive('from')->andReturn($pipeline);

    \Moneo\LaravelRag\Facades\Rag::shouldReceive('from')
        ->with('App\\Models\\Document')
        ->andReturn($pipeline);

    $registrar = new McpToolRegistrar($server, 'App\\Models\\Document');
    $registrar->as('docs')->description('Search docs')->expose();

    $tools = $server->getTools();
    expect($tools)->toHaveKey('docs')
        ->and($tools['docs']['description'])->toBe('Search docs')
        ->and($tools['docs']['model'])->toBe('App\\Models\\Document');
});

test('default name is lowercase class basename', function () {
    $server = new RagMcpServer;
    $registrar = new McpToolRegistrar($server, 'App\\Models\\FooBarDocument');

    // Expose with default name
    $pipeline = Mockery::mock(RagPipeline::class);
    \Moneo\LaravelRag\Facades\Rag::shouldReceive('from')->andReturn($pipeline);

    $registrar->expose();

    expect($server->getTools())->toHaveKey('foobardocument');
});
