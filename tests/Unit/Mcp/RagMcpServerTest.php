<?php

declare(strict_types=1);

use Moneo\LaravelRag\Mcp\RagMcpServer;
use Moneo\LaravelRag\Pipeline\RagPipeline;

test('handles initialize request', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => []]);

    expect($response['result']['serverInfo']['name'])->toBe('laravel-rag')
        ->and($response['result']['protocolVersion'])->toBe('2024-11-05')
        ->and($response['jsonrpc'])->toBe('2.0')
        ->and($response['id'])->toBe(1);
});

test('returns empty tools list when none registered', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list', 'params' => []]);

    expect($response['result']['tools'])->toBeEmpty();
});

test('returns error for unknown method', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 3, 'method' => 'unknown', 'params' => []]);

    expect($response)->toHaveKey('error')
        ->and($response['error']['code'])->toBe(-32601);
});

test('returns error for unknown tool call', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest([
        'jsonrpc' => '2.0', 'id' => 4, 'method' => 'tools/call',
        'params' => ['name' => 'nonexistent_search', 'arguments' => []],
    ]);

    expect($response)->toHaveKey('error')
        ->and($response['error']['code'])->toBe(-32602);
});

test('addTool stores tool definition', function () {
    $server = new RagMcpServer;
    $pipeline = Mockery::mock(RagPipeline::class);

    $server->addTool('docs', 'Search docs', 'App\\Models\\Doc', $pipeline);

    expect($server->getTools())->toHaveKey('docs')
        ->and($server->getTools()['docs']['name'])->toBe('docs');
});

test('tools/list includes registered tools', function () {
    $server = new RagMcpServer;
    $pipeline = Mockery::mock(RagPipeline::class);
    $server->addTool('kb', 'Knowledge base', 'App\\Models\\Doc', $pipeline);

    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 5, 'method' => 'tools/list', 'params' => []]);

    $tools = $response['result']['tools'];
    expect($tools)->toHaveCount(2); // kb_search + kb_ask
    $names = array_column($tools, 'name');
    expect($names)->toContain('kb_search', 'kb_ask');
});

test('tool schema has required fields', function () {
    $server = new RagMcpServer;
    $pipeline = Mockery::mock(RagPipeline::class);
    $server->addTool('docs', 'Search', 'App\\Models\\Doc', $pipeline);

    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 6, 'method' => 'tools/list', 'params' => []]);

    $searchTool = $response['result']['tools'][0];
    expect($searchTool['inputSchema']['type'])->toBe('object')
        ->and($searchTool['inputSchema']['required'])->toContain('query');
});

test('handles missing method key gracefully', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 7]);

    expect($response)->toHaveKey('error');
});

test('handles missing id gracefully', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'method' => 'initialize']);

    expect($response['id'])->toBeNull()
        ->and($response['result'])->toBeArray();
});

test('getTools returns empty by default', function () {
    expect((new RagMcpServer)->getTools())->toBeEmpty();
});

test('register returns McpToolRegistrar', function () {
    $server = new RagMcpServer;
    $registrar = $server->register('App\\Models\\Document');

    expect($registrar)->toBeInstanceOf(\Moneo\LaravelRag\Mcp\McpToolRegistrar::class);
});
