<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Chunking\Strategies;

interface ChunkerContract
{
    /**
     * Split text into chunks.
     *
     * @param  string  $text  The text to chunk
     * @param  array<string, mixed>  $options  Strategy-specific options
     * @return array<int, string>  The chunks
     */
    public function chunk(string $text, array $options = []): array;
}
