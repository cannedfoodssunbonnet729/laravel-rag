<?php

declare(strict_types=1);

use Moneo\LaravelRag\Mcp\RagMcpServer;

/**
 * Chaos tests for MCP server — malformed JSON-RPC inputs.
 *
 * @group chaos
 */

test('handles completely empty request', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest([]);

    expect($response)->toHaveKey('error')
        ->and($response['error']['code'])->toBe(-32601);
});

test('handles request with null method', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 1, 'method' => null]);

    expect($response)->toHaveKey('error');
});

test('handles request with numeric method', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 1, 'method' => 42]);

    expect($response)->toHaveKey('error');
});

test('handles tools/call with missing params', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call']);

    expect($response)->toHaveKey('error');
});

test('handles tools/call with empty arguments', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest([
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => '', 'arguments' => []],
    ]);

    expect($response)->toHaveKey('error');
});

test('handles tools/call with injection in tool name', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest([
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => "'; DROP TABLE--", 'arguments' => []],
    ]);

    expect($response)->toHaveKey('error')
        ->and($response['error']['code'])->toBe(-32602);
});

test('handles request with very large id', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest([
        'jsonrpc' => '2.0',
        'id' => PHP_INT_MAX,
        'method' => 'initialize',
    ]);

    expect($response['id'])->toBe(PHP_INT_MAX)
        ->and($response)->toHaveKey('result');
});

test('handles request with string id', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest([
        'jsonrpc' => '2.0',
        'id' => 'string-id-123',
        'method' => 'initialize',
    ]);

    expect($response['id'])->toBe('string-id-123');
});

test('always returns valid JSON-RPC structure', function () {
    $server = new RagMcpServer;

    $malformedRequests = [
        [],
        ['method' => 'bad'],
        ['jsonrpc' => '1.0', 'method' => 'initialize'],
        ['jsonrpc' => '2.0', 'method' => str_repeat('x', 10000)],
    ];

    foreach ($malformedRequests as $request) {
        $response = $server->handleRequest($request);

        expect($response)->toHaveKey('jsonrpc')
            ->and($response['jsonrpc'])->toBe('2.0');

        // Must have either 'result' or 'error', never both, never neither
        $hasResult = array_key_exists('result', $response);
        $hasError = array_key_exists('error', $response);
        expect($hasResult || $hasError)->toBeTrue('Response must have result or error');
    }
});

test('error responses always have code and message', function () {
    $server = new RagMcpServer;
    $response = $server->handleRequest([
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'nonexistent',
    ]);

    expect($response['error'])->toHaveKeys(['code', 'message'])
        ->and($response['error']['code'])->toBeInt()
        ->and($response['error']['message'])->toBeString();
});
