# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability in `moneo/laravel-rag`, please report it responsibly:

1. **Do NOT open a public GitHub issue.**
2. Email: security@moneo.dev (or use [GitHub Security Advisories](https://github.com/moneo/laravel-rag/security/advisories/new))
3. Include:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

We will acknowledge receipt within 48 hours and provide an initial assessment within 5 business days.

## Security Measures in This Package

### Input Sanitisation
All user-supplied text passed to LLMs goes through `InputSanitiser::clean()`, which strips 40+ known prompt injection patterns.

### Vector Validation
All embedding vectors are validated via `VectorValidator::validate()` before storage — checking dimensions, NaN, and infinity values.

### Cache Integrity
Cache keys are HMAC-signed with the application key. Corrupted or tampered cache entries are automatically detected and evicted.

### SQL Injection Protection
Table names are validated against a strict regex (`/\A[a-zA-Z_][a-zA-Z0-9_.]*\z/`) before any SQL interpolation.

### Supply Chain
- All releases include a CycloneDX SBOM
- GitHub Artifact Attestations provide Sigstore provenance
- All dependencies are MIT/Apache-2.0/BSD licensed
- `composer audit` runs in CI on every push

## Dependency Policy

- Zero GPL/LGPL/AGPL dependencies (incompatible with MIT distribution)
- `composer audit` must report zero known vulnerabilities
- Dependabot monitors weekly for security updates
