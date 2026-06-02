# Installation Guide

This guide will help you install and set up the `clubdeuce/tessitura` project for local development.

## Prerequisites

- **PHP 8.0+**
- **Composer** (Dependency Manager for PHP)
- **Redis** (if using RedisCache)

## 1. Clone the Repository

```bash
git clone <repository-url>
cd tessitura
```

## 2. Install Dependencies

Use the project Make target (preferred):

```bash
make install
```

Or run Composer directly:

```bash
composer install
```

## 3. Configuration

- Provide Tessitura API settings in your application code:
  - `base_route`
  - `username`
  - `password`
  - `machine`
  - `usergroup`
  - `version` (optional, defaults to `16`)

You can use either `Base\Container` or the `Tessitura` facade to initialize services.

## 4. Running Tests

Run the full suite:

```bash
make test
```

Or run PHPUnit directly:

```bash
vendor/bin/phpunit -c tests/phpunit.xml.dist
```

Run a single test file:

```bash
vendor/bin/phpunit -c tests/phpunit.xml.dist tests/unit/ApiTest.php
```

## 5. Code Quality Tools

Preferred commands:

```bash
make static-analysis
make phpstan
make phpcs
make phpmd
make php-cs-fixer
make fix
make validate
```

## 6. Caching

If you plan to use Redis caching, ensure Redis is running and accessible. Configure connection details as needed in your application.

Note: only successful `GET` responses are cached when a cache implementation is provided.

## 7. Additional Resources

- [Getting Started](getting-started.md)
- [Caching](caching.md)
- [Static Analysis](static-analysis.md)
- [Magic Methods Migration](magic-methods-migration.md)

---

For further help, consult the README.md or open an issue in the repository.
