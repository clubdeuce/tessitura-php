# Static Analysis Tools

This project uses several static analysis tools to maintain code quality and consistency.

## Available Tools

### PHPStan
Static analysis tool that finds bugs in your code without running it.

**Configuration:** `phpstan.neon`

**Run manually:**
```bash
vendor/bin/phpstan analyse --memory-limit=2G
# or
make phpstan
```

### PHP CodeSniffer (PHPCS)
Detects violations of a defined set of coding standards.

**Configuration:** `phpcs.xml`

**Run manually:**
```bash
vendor/bin/phpcs
# or  
make phpcs
```

**Auto-fix issues:**
```bash
vendor/bin/phpcbf
```

### PHP-CS-Fixer
Automatically fixes PHP coding standards issues.

**Configuration:** `.php-cs-fixer.php`

**Run manually (dry-run):**
```bash
vendor/bin/php-cs-fixer fix --dry-run --diff
# or
make php-cs-fixer
```

**Auto-fix issues:**
```bash
vendor/bin/php-cs-fixer fix
# or
make fix
```

### PHPMD (PHP Mess Detector)
Looks for potential problems in your code.

**Run manually:**
```bash
vendor/bin/phpmd src,tests text cleancode,codesize,controversial,design,naming,unusedcode
# or
make phpmd
```

## Quick Commands

```bash
# Install dependencies
make install

# Run all static analysis tools
make static-analysis

# Run tests
make test

# Auto-fix code style issues
make fix

# Clean cache
make clean
```

## Continuous Integration

All tools run automatically on:
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches

The CI pipeline tests against PHP versions 8.0, 8.1, 8.2, and 8.3.

## Configuration Notes

- PHPStan is configured at level 6 for a good balance of strictness and practicality
- PHPCS uses PSR-12 coding standard with some additional rules
- PHP-CS-Fixer is configured to match PSR-12 and apply some modern PHP practices
- Magic methods are allowed where needed for API flexibility
- Some legacy patterns (like `trigger_error`) are permitted for backward compatibility