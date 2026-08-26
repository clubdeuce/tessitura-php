# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
make install          # Install dependencies
make test             # Run PHPUnit tests
make static-analysis  # Run all static analysis (phpstan, phpcs, phpmd, php-cs-fixer)
make fix              # Auto-fix code style issues
```

Run a single test file:
```bash
vendor/bin/phpunit -c tests/phpunit.xml.dist tests/unit/ApiTest.php
```

Run a single test method:
```bash
vendor/bin/phpunit -c tests/phpunit.xml.dist --filter testCacheHit tests/unit/ApiCacheTest.php
```

`tests/integration/RedisCacheTest.php` requires a local Redis instance reachable at Predis defaults.

## Architecture

This is a PHP library (`Clubdeuce\Tessitura`) that wraps the Tessitura REST API. There are two request paths — a modern container-based path and an older direct-client path.

### Modern path (use this for new work)

1. **`Container`** (`src/Base/Container.php`) — lazy service container. Instantiate with config params (`base_route`, `username`, `password`, `machine`, `usergroup`, `version`). Wires `http_client`, `logger`, `api`, and all resource services. An optional pre-registered `cache` service is passed into `Api`.
2. **`Api`** (`src/Helpers/Api.php`) — central HTTP layer. Builds the Tessitura Basic auth header (`username:usergroup:machine:password` base64-encoded), dispatches GET/POST via Guzzle, logs via PSR-3, and caches only successful GET responses when a `CacheInterface` is attached. Cache keys include endpoint, base route, API version, and request args (excluding `method` and `cache_expiration`).
3. **Resource classes** (`src/Resources/`) — implement `ResourceInterface`, depend on `ApiInterface`, translate domain methods into endpoint calls, and return typed objects. `Performances` is the canonical example for new resources.

### Legacy path

`Seasons` (`src/Resources/Seasons.php`) talks directly to a Guzzle client, bypassing `Api`, container wiring, logging, and caching. Preserve this pattern when modifying `Seasons`/`Season` unless deliberately migrating it.

### Hydration layer

`Base` (`src/Base/Base.php`) stores raw Tessitura response fields in `$extraArgs`. `setState()` maps constructor args to matching properties (plain name or `_`-prefixed protected property) and stores everything else in `extraArgs`. Resource accessors read from `extraArgs` directly (e.g., `$this->extraArgs['PerformanceId']`).

`Resource` (`src/Base/Resource.php`) extends `Base` and adds typed `getId()`/`getDescription()` accessors over `extraArgs['Id']` and `extraArgs['Description']`.

## Key conventions

- **Config naming is inconsistent**: `Container` and docs use `base_route`; parts of `Api` construction use `baseRoute`. Keep existing call sites aligned rather than normalizing in isolation.
- **Property naming**: Existing resources use underscore-prefixed protected properties (`$_api`, `$_availableCount`). Keep this pattern in classes hydrated through `Base`.
- **Cache behavior**: Only successful GET responses are cached. Override `cache_expiration` per-request (default 10 minutes); `Performances` uses 5 minutes for price/zone lookups.
- **Tests**: Use PHPUnit 11 attribute metadata (`#[CoversClass]`, `#[UsesClass]`, `#[Depends]`) and fixture files under `tests/fixtures/` plus mocked Guzzle clients. Test namespace casing is not normalized — match the surrounding file's style.
- **`makePerformance()`** in `Performances` is a factory method intentionally overridable in subclasses to produce enriched instances.
