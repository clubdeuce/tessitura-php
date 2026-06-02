# Tessitura API PHP Library

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/clubdeuce/tessitura-php/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/clubdeuce/tessitura-php/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/clubdeuce/tessitura-php/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/clubdeuce/tessitura-php/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/clubdeuce/tessitura-php/badges/build.png?b=main)](https://scrutinizer-ci.com/g/clubdeuce/tessitura-php/build-status/main)
[![codecov](https://codecov.io/gh/clubdeuce/tessitura-php/graph/badge.svg?token=B3JQ368TP6)](https://codecov.io/gh/clubdeuce/tessitura-php)

A PHP library for the Tessitura API.

This is not an exhaustive implementation, but rather a subset of functionality necessary to implement projects for
past and present clients. Functionality is added on an as-needed basis, but contributions are welcome.

## Architecture Overview

The codebase has two request paths:

- Modern container-backed path: `Container` -> `Api` -> resource classes in `src/Resources/`
- Legacy direct-client path: `Seasons` and related classes that call Guzzle directly

For new work, prefer the container-backed path. `src/Resources/Performances.php` is the canonical pattern.

## Project Structure

Key directories:

- `src/Base/` - hydration layer and lightweight service container
- `src/Helpers/` - HTTP API helper and request behavior
- `src/Resources/` - resource services and resource objects
- `src/Cache/` - cache implementations (`ArrayCache`, `RedisCache`)
- `src/Interfaces/` - API, cache, logger, and resource contracts
- `tests/unit/` and `tests/integration/` - test suites
- `docs/` - developer and usage documentation

## Development Commands

```bash
make install
make test
make static-analysis
make fix
make validate
```

## Documentation

- [Getting Started](docs/getting-started.md) — installation and basic usage
- [Caching](docs/caching.md) — reduce API calls with Redis or in-memory caching
- [Installation](docs/installation.md) — local setup and common workflows
- [Static Analysis](docs/static-analysis.md) — linting and quality tooling
- [Magic Methods Migration](docs/magic-methods-migration.md) — moving to explicit accessors
- [Tasks and Roadmap](docs/tasks.md) — backlog and long-term improvements
