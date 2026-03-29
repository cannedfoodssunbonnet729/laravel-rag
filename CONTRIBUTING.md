# Contributing to Laravel RAG

Thank you for considering contributing to `moneo/laravel-rag`.

## Development Setup

```bash
git clone https://github.com/moneo/laravel-rag.git
cd laravel-rag
composer install
```

## Quality Gates

All of these must pass before a PR is merged:

```bash
# Code style (auto-fix)
composer format

# Static analysis (PHPStan level 9)
composer analyse

# Tests (393+ assertions across 12 layers)
composer test

# Test coverage (≥ 99%)
composer test:coverage

# Mutation testing (MSI ≥ 85%)
composer infection
```

## Pull Request Process

1. Fork the repo and create a feature branch from `main`
2. Write tests first — every new feature needs unit + feature tests
3. Follow [Laravel coding style](https://laravel.com/docs/contributions#coding-style) — enforced by Pint
4. Run all quality gates locally before pushing
5. Submit a PR with a clear description of what and why

## Test Layers

| Layer | Directory | Purpose |
|---|---|---|
| Unit | `tests/Unit/` | Every class in isolation, external deps mocked |
| Feature | `tests/Feature/` | Full flows with Testbench |
| Contract | `tests/Contract/` | VectorStore driver compliance |
| Architecture | `tests/Architecture/` | Structural constraints via `arch()` |
| Property | `tests/Property/` | Random input invariant proofs |
| Chaos | `tests/Chaos/` | Fault injection scenarios |
| Fuzz | `tests/Fuzz/` | Adversarial/malformed inputs |
| Concurrency | `tests/Concurrency/` | Race condition detection |
| Memory | `tests/Memory/` | Memory leak detection |
| Snapshots | `tests/Snapshots/` | Output format stability |
| Regression | `tests/Regression/` | Known-good scenario validation |
| Benchmarks | `tests/Benchmarks/` | Performance tracking |

## Adding a Custom Vector Store Driver

Community drivers should:
- Follow naming: `moneo/laravel-rag-{driver}`
- Implement `VectorStoreContract`
- Extend `VectorStoreContractTest` to prove compliance
- Target MSI ≥ 90% for driver code

## Security

If you discover a security vulnerability, please see [SECURITY.md](docs/SECURITY.md) for responsible disclosure instructions. Do NOT open a public issue.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
