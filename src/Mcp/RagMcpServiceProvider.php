<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Mcp;

use Illuminate\Support\ServiceProvider;

class RagMcpServiceProvider extends ServiceProvider
{
    /**
     * Register MCP services.
     */
    public function register(): void
    {
        $this->app->singleton(RagMcpServer::class);
    }

    /**
     * Bootstrap MCP services.
     */
    public function boot(): void
    {
        if (! config('rag.mcp.enabled')) {
            return;
        }

        // MCP server is available via the container
        // Tools are registered via RagMcp::register() in the app's service provider
    }
}
