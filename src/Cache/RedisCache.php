<?php

namespace Clubdeuce\Tessitura\Cache;

use Clubdeuce\Tessitura\Interfaces\CacheInterface;
use Predis\Client;
use Exception;

class RedisCache implements CacheInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Get a value from the cache.
     *
     * @param string $key The cache key
     * @return mixed|null The cached value or null if not found
     */
    public function get(string $key): mixed
    {
        try {
            $value = $this->redis->get($key);
            if ($value === null) {
                return null;
            }
            return json_decode($value, true);
        } catch (Exception) {
            return null;
        }
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
        try {
            $serializedValue = json_encode($value);
            if ($ttl > 0) {
                return $this->redis->setex($key, $ttl, $serializedValue) == 'OK';
            } else {
                return $this->redis->set($key, $serializedValue) == 'OK';
            }
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key The cache key
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool
    {
        try {
            return $this->redis->exists($key) > 0;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Delete a value from the cache.
     *
     * @param string $key The cache key
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool
    {
        try {
            return $this->redis->del($key) > 0;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Clear all cached values.
     *
     * @return bool True on success, false on failure
     */
    public function clear(): bool
    {
        try {
            return $this->redis->flushdb() === 'OK';
        } catch (Exception) {
            return false;
        }
    }
}