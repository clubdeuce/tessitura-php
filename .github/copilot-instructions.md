# Copilot Instructions

## Build, test, and lint commands

Install dependencies first:

```bash
make install
```

Primary project commands from `Makefile`:

```bash
make test
make static-analysis
make phpstan
make phpcs
make phpmd
make php-cs-fixer
make fix
make validate
```

Run the full PHPUnit suite directly:

```bash
vendor/bin/phpunit -c tests/phpunit.xml.dist
```

Run a single test file:

```bash
vendor/bin/phpunit -c tests/phpunit.xml.dist tests/unit/ApiTest.php
```

Run a single test method:

```bash
vendor/bin/phpunit -c tests/phpunit.xml.dist --filter testCacheHit tests/unit/ApiCacheTest.php
```

Integration tests are mostly fixture-backed, but `tests/integration/RedisCacheTest.php` expects a local Redis reachable by Predis defaults.

## High-level architecture

The library has one main request path and one older direct-client path:

- `src/Helpers/Api.php` is the central HTTP layer. It builds the Tessitura Basic auth header, dispatches `GET`/`POST` requests through Guzzle, logs through PSR-3 when a logger is present, and caches only successful `GET` responses when a `CacheInterface` implementation is attached.
- `src/Base/Container.php` is the lazy service container. It wires `http_client`, `logger`, `api`, and `performances`, and it passes an optional pre-registered `cache` service into the API helper.
- `src/Resources/Performances.php` is the main resource pattern for new work: it depends on `ApiInterface`, translates domain methods into endpoint calls, and returns typed resource objects such as `Performance` and `PerformanceZoneAvailability`.
- `src/Resources/Seasons.php` is a different, older pattern: it talks directly to a Guzzle client and bypasses `Api`, container wiring, logging, and caching. When changing resource code, preserve the pattern already used by that resource unless you are intentionally migrating it.
- `src/Base/Base.php` and `src/Base/Resource.php` are the hydration layer. Constructors keep raw Tessitura response fields in `extraArgs`, while resource classes expose typed accessors over those payloads.

## Key conventions

- Prefer the container-oriented flow for API-backed features: configuration -> `Container` -> `Api` -> resource class. Reuse `ApiInterface` instead of creating ad hoc HTTP code in new resources.
- Resource objects usually wrap Tessitura response keys directly instead of normalizing payload shape up front. Add explicit accessors around `extraArgs()` rather than replacing raw payload storage.
- `Base::setState()` supports both plain property names and underscore-prefixed protected properties. Existing resources rely on underscore-prefixed fields such as `$_api`, `$_availableCount`, and `$_zone`; keep that pattern where a class is hydrated through `Base`.
- Config naming is not fully uniform. `Container` and most docs use `base_route`, while parts of `Api` construction and some tests use `baseRoute`. When touching config flow, keep the existing call sites aligned instead of standardizing one side in isolation.
- Cache behavior is specific: only successful `GET` calls are cached, and cache keys include endpoint, base route, API version, and request args with `method` and `cache_expiration` excluded.
- Tests use PHPUnit 11 attribute metadata (`#[CoversClass]`, `#[UsesClass]`, `#[Depends]`) and mostly rely on fixtures under `tests/fixtures` plus mocked Guzzle clients instead of live Tessitura calls.
- Test namespace casing is not fully normalized (`Unit`, `unit`, `Integration`, `integration`). Match the surrounding test file's style instead of renaming unrelated namespaces while making a focused change.
