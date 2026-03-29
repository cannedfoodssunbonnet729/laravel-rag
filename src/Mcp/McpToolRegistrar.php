<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Mcp;

use Moneo\LaravelRag\Facades\Rag;
use Moneo\LaravelRag\Pipeline\RagPipeline;

class McpToolRegistrar
{
    protected string $name;

    protected string $description = '';

    protected ?RagPipeline $pipeline = null;

    public function __construct(
        protected readonly RagMcpServer $server,
        protected readonly string $modelClass,
    ) {
        $this->name = strtolower(class_basename($this->modelClass));
    }

    /**
     * Set the tool name.
     *
     * @return $this
     */
    public function as(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the tool description.
     *
     * @return $this
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Register and expose the tool.
     */
    public function expose(): void
    {
        $pipeline = $this->pipeline ?? Rag::from($this->modelClass);

        $this->server->addTool(
            name: $this->name,
            description: $this->description ?: "Search {$this->modelClass}",
            modelClass: $this->modelClass,
            pipeline: $pipeline,
        );
    }
}
