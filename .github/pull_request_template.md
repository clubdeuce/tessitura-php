---
name: Pull Request
about: Submit a pull request
title: ''
labels: ''
assignees: ''

---

## Description
<!-- Brief description of the changes -->

## Type of Change
<!-- Mark the relevant option(s) with an "x" -->

- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Code quality improvement
- [ ] Performance improvement

## Testing
<!-- Describe how you tested your changes -->

- [ ] Tests pass locally
- [ ] Static analysis tools pass (PHPStan, PHPCS, PHPMD, PHP-CS-Fixer)
- [ ] Code coverage maintained or improved
- [ ] Manual testing performed (if applicable)

## Static Analysis Checklist
<!-- Run these commands before submitting -->

- [ ] `make phpstan` - PHPStan analysis passes
- [ ] `make phpcs` - PHP CodeSniffer passes
- [ ] `make phpmd` - PHP Mess Detector passes
- [ ] `make php-cs-fixer` - PHP-CS-Fixer passes (dry-run)
- [ ] `make test` - All tests pass

Quick command: `make static-analysis && make test`

## Documentation
<!-- If applicable -->

- [ ] Code is properly commented
- [ ] Public API changes are documented
- [ ] README updated (if needed)
- [ ] CHANGELOG updated (if needed)

## Checklist

- [ ] My code follows the code style of this project
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings or errors
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes