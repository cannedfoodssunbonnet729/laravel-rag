<?php

declare(strict_types=1);

use Moneo\LaravelRag\Memory\ThreadMessage;

test('ThreadMessage has correct table name', function () {
    $msg = new ThreadMessage;

    expect($msg->getTable())->toBe('rag_thread_messages');
});

test('ThreadMessage has correct fillable fields', function () {
    $msg = new ThreadMessage;

    expect($msg->getFillable())->toContain('thread_id', 'role', 'content', 'tokens', 'metadata');
});

test('ThreadMessage casts tokens to integer', function () {
    $msg = new ThreadMessage;

    expect($msg->getCasts()['tokens'])->toBe('integer');
});

test('ThreadMessage casts metadata to array', function () {
    $msg = new ThreadMessage;

    expect($msg->getCasts()['metadata'])->toBe('array');
});
