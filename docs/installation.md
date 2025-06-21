# Installation Guide

This guide will help you install and set up the tessitura-php project.

## Prerequisites

- **PHP 8.0+**
- **Composer** (Dependency Manager for PHP)
- **Redis** (if using RedisCache)

## 1. Clone the Repository

```
git clone <repository-url>
cd tessitura-php
```

## 2. Install Dependencies

Use Composer to install PHP dependencies:

```
composer install
```

## 3. Configuration

- Copy any example configuration files (if provided) and update them as needed.
- Review `phpstan.neon`, `psalm.xml`, and `phpcs.xml` for static analysis and coding standards configuration.

## 4. Running Tests

To run the test suite:

```
cd tests
../vendor/bin/phpunit
```

## 5. Code Quality Tools

- **PHPStan:**
  ```
  vendor/bin/phpstan analyse src
  ```
- **PHPCS:**
  ```
  vendor/bin/phpcs src
  ```
- **PHP CS Fixer**
  ```
  vendor/bin/php-cs-fixer fix src --dry-run
  ```

## 6. Caching

If you plan to use Redis caching, ensure Redis is running and accessible. Configure connection details as needed in your application.

## 7. Additional Resources

- See other documentation in the `docs/` directory for advanced topics.

---

For further help, consult the README.md or open an issue in the repository.

