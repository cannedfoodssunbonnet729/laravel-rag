<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Exceptions;

class MissingApiKeyException extends RagException
{
    public function __construct(string $provider = 'openai')
    {
        parent::__construct(
            "API key for '{$provider}' is not configured. "
            ."Set OPENAI_API_KEY in your .env file. "
            .'Get your key at: https://platform.openai.com/api-keys'
        );
    }
}
