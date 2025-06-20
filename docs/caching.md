# Tessitura API Caching Layer

This document describes the caching functionality added to the Tessitura PHP library.

## Overview

The caching layer reduces API calls and improves performance by storing successful GET responses in a cache (Redis or in-memory). Only GET requests are cached - POST, PUT, DELETE and other write operations are never cached.

## Cache Implementations

### RedisCache

Uses Redis as the caching backend via the Predis client.

```php
use Clubdeuce\Tessitura\Cache\RedisCache;
use Predis\Client;

// Create Redis client
$redis = new Client([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

// Create cache instance
$cache = new RedisCache($redis);
```

### ArrayCache

Simple in-memory cache for testing and development.

```php
use Clubdeuce\Tessitura\Cache\ArrayCache;

$cache = new ArrayCache();
```

## Using Cache with API

### Basic Usage

```php
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Cache\RedisCache;
use Predis\Client;

// Set up Redis cache
$redis = new Client(['host' => '127.0.0.1']);
$cache = new RedisCache($redis);

// Create API instance with cache
$api = new Api(
    [
        'baseRoute' => 'https://api.tessitura.com/',
        'username' => 'your_username',
        'password' => 'your_password',
        'machine' => 'your_machine',
        'usergroup' => 'your_usergroup',
    ],
    null, // HTTP client (will be auto-created)
    null, // Logger (optional)
    $cache // Cache instance
);

// First call hits the API
$performances1 = $api->get('performances');

// Second call returns cached data
$performances2 = $api->get('performances');
```

### Custom Cache Expiration

```php
// Cache for 30 minutes (1800 seconds)
$result = $api->get('seasons', ['cache_expiration' => 1800]);

// Cache for 5 minutes (300 seconds)
$result = $api->get('productions', ['cache_expiration' => 300]);
```

### Using with Container

```php
use Clubdeuce\Tessitura\Base\Container;
use Clubdeuce\Tessitura\Cache\RedisCache;
use Predis\Client;

$container = new Container([
    'base_route' => 'https://api.tessitura.com/',
    'username' => 'your_username',
    'password' => 'your_password',
    'machine' => 'your_machine',
    'usergroup' => 'your_usergroup',
]);

// Set up cache
$redis = new Client(['host' => '127.0.0.1']);
$cache = new RedisCache($redis);
$container->set('cache', $cache);

// Get API instance with cache
$api = $container->get('api');
```

## Cache Behavior

### What Gets Cached

- Only GET requests are cached
- Only successful responses (HTTP 200) are cached
- Cache keys include endpoint, base route, API version, and request parameters

### What Doesn't Get Cached

- POST, PUT, DELETE, and other write operations
- Failed responses (non-200 status codes)
- Requests when no cache instance is provided

### Cache Key Generation

Cache keys are generated based on:
- API endpoint
- Base route
- API version
- Request parameters (excluding method and cache_expiration)

Example key: `tessitura:abc123def456...` (MD5 hash of the above data)

## Cache Interface

All cache implementations must implement `CacheInterface`:

```php
interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): bool;
    public function has(string $key): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
}
```

## Creating Custom Cache Implementations

```php
use Clubdeuce\Tessitura\Interfaces\CacheInterface;

class MyCustomCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        // Your implementation
    }
    
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        // Your implementation
    }
    
    // ... implement other methods
}
```

## Configuration

### Default Cache Expiration

The default cache expiration is 10 minutes (600 seconds), defined in `Api::CACHE_EXPIRATION_DEFAULT`.

### Redis Configuration

For production use, configure Redis appropriately:

```php
$redis = new Client([
    'scheme' => 'tcp',
    'host'   => 'your-redis-host',
    'port'   => 6379,
    'password' => 'your-redis-password',
    'database' => 0,
]);
```

## Error Handling

Cache operations are designed to fail gracefully:
- If cache is unavailable, requests go directly to the API
- Cache errors are silently ignored
- Failed cache operations don't prevent API calls

## Performance Considerations

- Use Redis for production environments
- Set appropriate TTL values based on data freshness requirements
- Monitor cache hit rates to optimize cache settings
- Consider cache invalidation strategies for frequently updated data

## Testing

The library includes both unit and integration tests for cache functionality:

```bash
# Run cache-related tests
vendor/bin/phpunit tests/unit/ApiCacheTest.php
vendor/bin/phpunit tests/integration/ApiCacheIntegrationTest.php
```

## Dependencies

- `predis/predis` ^2.3 for Redis support
- Redis server for RedisCache implementation