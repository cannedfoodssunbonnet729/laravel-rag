<?php

declare(strict_types=1);

use Moneo\LaravelRag\Mcp\RagMcpServer;
use Moneo\LaravelRag\Pipeline\RagPipeline;

test('MCP initialize response schema is stable', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => []]);

    expect($response)->toHaveKeys(['jsonrpc', 'id', 'result'])
        ->and($response['result'])->toHaveKeys(['protocolVersion', 'capabilities', 'serverInfo'])
        ->and($response['result']['serverInfo'])->toHaveKeys(['name', 'version'])
        ->and($response['result']['capabilities'])->toHaveKey('tools');
});

test('MCP tool schema structure is stable', function () {
    $server = new RagMcpServer;
    $pipeline = Mockery::mock(RagPipeline::class);
    $server->addTool('test', 'Test tool', 'App\\Models\\Test', $pipeline);

    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list', 'params' => []]);

    $tools = $response['result']['tools'];
    expect($tools)->toHaveCount(2);

    $searchTool = $tools[0];
    expect($searchTool)->toHaveKeys(['name', 'description', 'inputSchema'])
        ->and($searchTool['inputSchema'])->toHaveKeys(['type', 'properties', 'required'])
        ->and($searchTool['inputSchema']['properties'])->toHaveKey('query');
});

test('MCP error response schema is stable', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 3, 'method' => 'bad', 'params' => []]);

    expect($response)->toHaveKeys(['jsonrpc', 'id', 'error'])
        ->and($response['error'])->toHaveKeys(['code', 'message']);
});
