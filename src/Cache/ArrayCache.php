<?php

namespace Clubdeuce\Tessitura\Cache;

use Clubdeuce\Tessitura\Interfaces\CacheInterface;

class ArrayCache implements CacheInterface
{
    /**
     * Cached values
     *
     * @var mixed[]
     */
    private array $cache = [];

    /**
     * Expiration times for cached values
     *
     * @var int[]
     */
    private array $expiration = [];

    /**
     * Get a value from the cache.
     *
     * @param string $key The cache key
     * @return mixed|null The cached value or null if not found
     */
    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        // Check if expired
        if (isset($this->expiration[$key]) && $this->expiration[$key] < time()) {
            unset($this->cache[$key], $this->expiration[$key]);

            return null;
        }

        return $this->cache[$key];
    }

    /**
     * Set a value in the cache.
     *
     * @param string $key The cache key
     * @param mixed $value The value to cache
     * @param int $ttl Time to live in seconds
     * @return bool True on success, false on failure
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $this->cache[$key] = $value;
        if ($ttl > 0) {
            $this->expiration[$key] = time() + $ttl;
        }

        return true;
    }

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key The cache key
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool
    {
        if (!array_key_exists($key, $this->cache)) {
            return false;
        }

        // Check if expired
        if (isset($this->expiration[$key]) && $this->expiration[$key] < time()) {
            unset($this->cache[$key], $this->expiration[$key]);

            return false;
        }

        return true;
    }

    /**
     * Delete a value from the cache.
     *
     * @param string $key The cache key
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expiration[$key]);

        return true;
    }

    /**
     * Clear all cached values.
     *
     * @return bool True on success, false on failure
     */
    public function clear(): bool
    {
        $this->cache      = [];
        $this->expiration = [];

        return true;
    }
}
