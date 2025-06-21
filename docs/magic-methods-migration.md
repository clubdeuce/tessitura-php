# Migration Guide: From Magic Methods to Explicit Methods

This guide helps you migrate from magic methods to explicit methods for better IDE support and type safety.

## What Changed

The Tessitura PHP library now provides explicit getter methods instead of relying solely on magic methods. This improves:

- **IDE Support**: Better autocomplete and type hints
- **Type Safety**: Explicit return types and parameter validation  
- **Code Clarity**: Clear method signatures and documentation
- **Static Analysis**: Better support for tools like PHPStan

## Migration Examples

### Before (Magic Methods)
```php
use Clubdeuce\Tessitura\Base\Base;

$resource = new Base(['Id' => 123, 'Name' => 'Sample']);

// Magic method calls (deprecated for common properties)
$id = $resource->id();           // ⚠️ Deprecated 
$name = $resource->name();       // ⚠️ Deprecated
$desc = $resource->description(); // ⚠️ Deprecated
```

### After (Explicit Methods)
```php
use Clubdeuce\Tessitura\Base\Base;

$resource = new Base(['Id' => 123, 'Name' => 'Sample']);

// Explicit method calls (recommended)
$id = $resource->getId();           // ✅ Recommended
$name = $resource->getName();       // ✅ Recommended  
$desc = $resource->getDescription(); // ✅ Recommended

// Generic getter for any property
$customValue = $resource->get('CustomField', 'default');
```

## ProductionSeason Changes

### Before
```php
// Relied on magic method
$response = $productionSeason->response(); // Magic method call
```

### After
```php
// Now has explicit method
$response = $productionSeason->response(); // Explicit method with proper return type
```

## Available Explicit Methods

### Base Class Methods

- `getId(): int` - Get ID value (checks both 'Id' and 'id' keys)
- `getName(): string` - Get name value (checks both 'Name' and 'name' keys)  
- `getDescription(): string` - Get description value (checks both 'Description' and 'description' keys)
- `get(string $key, mixed $default = null): mixed` - Get any value with optional default

### ProductionSeason Class Methods

- `response(): array` - Get response data with proper return type

## Backward Compatibility

Magic methods still work for backward compatibility:

```php
// These still work but show deprecation warnings for common properties
$id = $resource->id();     // Shows deprecation warning
$name = $resource->name(); // Shows deprecation warning

// Custom properties don't show warnings
$custom = $resource->customProperty(); // No warning
```

## Benefits

1. **Better IDE Support**: Your IDE can now provide accurate autocomplete and type hints
2. **Type Safety**: Methods have explicit return types
3. **Documentation**: Clear PHPDoc comments explain what each method returns
4. **Gradual Migration**: Existing code continues to work while you migrate
5. **Performance**: Slight performance improvement by avoiding magic method overhead

## Migration Timeline

- **Current**: Magic methods work with deprecation warnings for common properties
- **Recommended**: Start using explicit methods in new code
- **Future**: Magic methods may be removed in a future major version