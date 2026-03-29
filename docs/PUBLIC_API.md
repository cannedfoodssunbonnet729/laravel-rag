# Public API Surface — `moneo/laravel-rag`

This document lists every public class, method, and interface that is part of the package's
stable API contract. Breaking changes to these require a major version bump.

## Facades

- `Moneo\LaravelRag\Facades\Rag::from(string $modelClass): RagPipeline`
- `Moneo\LaravelRag\Facades\Ingest::file(string $path): IngestPipeline`
- `Moneo\LaravelRag\Facades\Ingest::text(string $content): IngestPipeline`
- `Moneo\LaravelRag\Facades\RagEval::suite(): RagEval`

## Contracts

- `Moneo\LaravelRag\VectorStores\Contracts\VectorStoreContract` — full interface
- `Moneo\LaravelRag\Chunking\Strategies\ChunkerContract` — full interface
- `Moneo\LaravelRag\Evals\Metrics\MetricContract` — full interface

## Pipeline

- `Moneo\LaravelRag\Pipeline\RagPipeline` — all public fluent methods
- `Moneo\LaravelRag\Pipeline\RagResult` — all public properties and methods
- `Moneo\LaravelRag\Pipeline\IngestPipeline` — all public fluent methods

## Traits

- `Moneo\LaravelRag\Concerns\HasVectorSearch` — all public methods
- `Moneo\LaravelRag\Concerns\AutoEmbeds` — all public methods

## Models

- `Moneo\LaravelRag\Memory\RagThread` — all public methods
- `Moneo\LaravelRag\Memory\ThreadMessage` — all public methods

## Events

- `Moneo\LaravelRag\Events\EmbeddingGenerated` — constructor signature
- `Moneo\LaravelRag\Events\EmbeddingCacheHit` — constructor signature

## Exceptions

- `Moneo\LaravelRag\Exceptions\RagException` — base class
- All exception subclasses — class names and inheritance hierarchy

## Configuration

- `config/rag.php` — all top-level keys and their types

---

Classes and methods NOT listed here are considered internal and may change without notice.
Internal classes are annotated with `@internal` in their docblock.
