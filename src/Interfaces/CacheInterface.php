<?php

namespace Clubdeuce\Tessitura\Interfaces;

interface CacheInterface
{
    /**
     * Get a value from the cache.
     *
     * @param string $key The cache key
     * @return mixed|null The cached value or null if not found
     */
    public function get(string $key): mixed;

    /**
     * Set a value in the cache.
     *
     * @param string $key The cache key
     * @param mixed $value The value to cache
     * @param int $ttl Time to live in seconds
     * @return bool True on success, false on failure
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key The cache key
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool;

    /**
     * Delete a value from the cache.
     *
     * @param string $key The cache key
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached values.
     *
     * @return bool True on success, false on failure
     */
    public function clear(): bool;
}
