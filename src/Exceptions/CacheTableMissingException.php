<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Exceptions;

class CacheTableMissingException extends RagException
{
    public function __construct()
    {
        parent::__construct(
            'The rag_embedding_cache table does not exist. Run: php artisan vendor:publish --tag=rag-migrations && php artisan migrate'
        );
    }
}
