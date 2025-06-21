.PHONY: help install test static-analysis phpstan phpcs phpmd php-cs-fixer fix clean validate

# Default target
help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies
	composer install

test: ## Run PHPUnit tests
	vendor/bin/phpunit -c tests/phpunit.xml.dist

static-analysis: phpstan phpcs phpmd php-cs-fixer ## Run all static analysis tools

phpstan: ## Run PHPStan static analysis
	vendor/bin/phpstan analyse --memory-limit=2G

phpcs: ## Run PHP CodeSniffer
	vendor/bin/phpcs

phpmd: ## Run PHP Mess Detector
	vendor/bin/phpmd src,tests text cleancode,codesize,controversial,design,naming,unusedcode

php-cs-fixer: ## Run PHP-CS-Fixer (dry-run)
	vendor/bin/php-cs-fixer fix --dry-run --diff

fix: ## Auto-fix code style issues
	vendor/bin/php-cs-fixer fix
	vendor/bin/phpcbf

validate: ## Validate static analysis setup
	./scripts/validate-setup.sh

clean: ## Clean cache directories
	rm -rf var/cache/*
	rm -rf .phpunit.cache