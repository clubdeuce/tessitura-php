#!/bin/bash

# Static Analysis Setup Validation Script

echo "🔍 Validating Static Analysis Setup..."
echo ""

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "❌ vendor/ directory not found. Run 'composer install' first."
    exit 1
fi

# Check configuration files
echo "📋 Checking configuration files..."

configs=(
    "phpstan.neon:PHPStan configuration"
    "phpcs.xml:PHP CodeSniffer configuration" 
    ".php-cs-fixer.php:PHP-CS-Fixer configuration"
    ".github/workflows/static-analysis.yml:GitHub Actions workflow"
)

for config in "${configs[@]}"; do
    file="${config%%:*}"
    desc="${config##*:}"
    
    if [ -f "$file" ]; then
        echo "✅ $desc ($file)"
    else
        echo "❌ Missing: $desc ($file)"
    fi
done

echo ""

# Check if binaries exist
echo "🔧 Checking tool binaries..."

tools=(
    "vendor/bin/phpstan:PHPStan"
    "vendor/bin/phpcs:PHP CodeSniffer"
    "vendor/bin/php-cs-fixer:PHP-CS-Fixer"
    "vendor/bin/phpmd:PHP Mess Detector"
)

for tool in "${tools[@]}"; do
    binary="${tool%%:*}"
    name="${tool##*:}"
    
    if [ -f "$binary" ]; then
        echo "✅ $name ($binary)"
    else
        echo "⚠️  Missing: $name ($binary) - try 'composer install'"
    fi
done

echo ""

# Check cache directories
echo "📂 Checking cache directories..."
if [ ! -d "var/cache" ]; then
    echo "ℹ️  Creating cache directory..."
    mkdir -p var/cache/phpstan
    echo "✅ Created var/cache/phpstan"
else
    echo "✅ Cache directory exists"
fi

echo ""
echo "🎉 Setup validation complete!"
echo ""
echo "Available commands:"
echo "  make install         - Install dependencies"
echo "  make static-analysis - Run all static analysis tools"
echo "  make phpstan         - Run PHPStan only"
echo "  make phpcs           - Run PHP CodeSniffer only"
echo "  make php-cs-fixer    - Run PHP-CS-Fixer (dry-run)"
echo "  make phpmd           - Run PHP Mess Detector only"
echo "  make fix             - Auto-fix code style issues"
echo "  make clean           - Clean cache directories"