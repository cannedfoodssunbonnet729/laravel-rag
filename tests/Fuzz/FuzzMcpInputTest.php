<?php

declare(strict_types=1);

use Moneo\LaravelRag\Mcp\RagMcpServer;

/**
 * Fuzz tests for MCP server — random JSON-RPC payloads.
 *
 * Must always return valid JSON-RPC, never expose stack traces.
 *
 * @group fuzz
 */

test('random JSON-RPC payloads always return valid JSON-RPC', function () {
    $server = new RagMcpServer;

    for ($i = 0; $i < 500; $i++) {
        $request = generateRandomJsonRpc();
        $response = $server->handleRequest($request);

        expect($response)->toBeArray()
            ->toHaveKey('jsonrpc')
            ->and($response['jsonrpc'])->toBe('2.0');

        // Must have result or error
        $hasResult = array_key_exists('result', $response);
        $hasError = array_key_exists('error', $response);
        expect($hasResult || $hasError)->toBeTrue();

        // Error responses must have code and message
        if ($hasError) {
            expect($response['error'])->toHaveKeys(['code', 'message']);
        }

        // Must never contain PHP stack traces
        $json = json_encode($response, JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json !== false) {
            expect($json)->not->toContain('Stack trace:')
                ->not->toContain('vendor/')
                ->not->toContain('.php:');
        }
    }
});

function generateRandomJsonRpc(): array
{
    $methods = ['initialize', 'tools/list', 'tools/call', '', null, 42, bin2hex(random_bytes(10))];
    $ids = [null, 0, 1, -1, PHP_INT_MAX, 'string-id', random_bytes(5)];

    $request = [
        'jsonrpc' => ['2.0', '1.0', '', null][random_int(0, 3)],
        'id' => $ids[random_int(0, count($ids) - 1)],
        'method' => $methods[random_int(0, count($methods) - 1)],
    ];

    if (random_int(0, 1)) {
        $request['params'] = generateRandomParams();
    }

    return $request;
}

function generateRandomParams(): array
{
    return match (random_int(0, 4)) {
        0 => [],
        1 => ['name' => bin2hex(random_bytes(5))],
        2 => ['name' => 'test_search', 'arguments' => ['query' => bin2hex(random_bytes(10))]],
        3 => ['name' => '', 'arguments' => []],
        4 => ['unexpected_key' => 'value', 'another' => random_int(0, 1000)],
    };
}
