# Changelog

All notable changes to `moneo/laravel-rag` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Exception hierarchy: `RagException` base with domain-specific subclasses (EmbeddingRateLimitException, EmbeddingTimeoutException, EmbeddingServiceException, EmbeddingResponseException, DimensionMismatchException, DeadlockException, VectorStoreLockException, GenerationException, CacheTableMissingException)
- `PrismRetryHandler`: exponential backoff with jitter for all Prism API calls (max 3 retries)
- `RagLogger`: structured logging with privacy-safe text hashing across all error paths
- `ScoreParser`: robust LLM score extraction using regex (handles "Score: 8", "8/10", trailing text)
- `InputSanitiser`: 40+ prompt injection patterns blocked
- `VectorValidator`: dimension, NaN, and infinity validation on every vector upsert
- `CacheIntegrityGuard`: HMAC-signed cache keys with auto-eviction of corrupted entries
- Config validation: dimensions > 0, chunk_size > 0, chunk_overlap < chunk_size enforced at boot
- DB transaction safety: SqliteVecStore upsert/delete/flush, IngestPipeline storeChunks, RagThread ask
- PgvectorStore deadlock retry with exponential backoff (max 3 attempts)
- Property-based tests (6 suites, 10K iterations each)
- Chaos tests (4 suites) for fault injection
- Fuzz tests (4 suites) for adversarial inputs
- Concurrency tests (2 suites) for race condition detection
- Memory leak tests (5 scenarios, 10K iterations)
- CI: psalm taint analysis, bc-check, license compliance, SBOM generation

### Fixed
- AutoEmbeds listener duplication (BUG-1): `saved` listener no longer re-registers inside `saving`
- IngestPipeline events with `stdClass` (BUG-2): events now accept `?Model`
- SQL injection via table names (BUG-3): strict regex validation
- CharacterChunker infinite loop (BUG-4): `overlap >= size` throws
- RagPipeline immutability (BUG-5): all fluent methods now clone
- SemanticChunker zero-vector division (BUG-6): explicit guard
- RagStream: error handling added — emits SSE error event instead of silently dying
- RagThread::ask(): wrapped in DB transaction — no orphan user messages on pipeline failure
- IngestPipeline::file(): throws `InvalidArgumentException` for nonexistent paths
- All 5 artisan commands: exception handling added
- Reranker + eval metrics: robust score parsing via ScoreParser (no more silent 0.0 on "Score: 8")
- All Prism calls now route through PrismRetryHandler (no more naked `Prism::` calls)

### Security
- HMAC-signed embedding cache keys prevent tamper attacks
- Input sanitisation blocks 40+ known prompt injection patterns
- Vector dimension + NaN/INF validation prevents corrupt storage
- Table name regex prevents SQL injection in dynamic queries
