<?php

namespace Clubdeuce\Tessitura\Tests\integration;

use Clubdeuce\Tessitura\Cache\RedisCache;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Predis\Client;

#[CoversClass(RedisCache::class)]
class RedisCacheTest extends testCase
{
    protected RedisCache $cache;

    protected function setUp(): void
    {
        $this->cache = new RedisCache(new Client());
    }

    public function testGetReturnsValueWhenKeyExists(): void
    {
        $this->cache->set('foo', 'bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    public function testGetReturnsNullWhenKeyDoesNotExist(): void
    {
        $this->assertNull($this->cache->get('missing'));
    }

    public function testSetStoresValue(): void
    {
        $this->assertTrue($this->cache->set('foo', 'bar'));
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    public function testSetWithZeroTTLStoresValue(): void
    {
        $this->assertTrue($this->cache->set('foo', 'bar', 0));
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    public function testHas(): void
    {
        $key = time();
        $this->assertFalse($this->cache->has($key), 'Cache should not have key before setting it');
        $this->assertTrue($this->cache->set($key, 'bar'), 'Setting key failed');
        $this->assertTrue($this->cache->has($key), 'Key was not found after setting it');
        $this->assertTrue($this->cache->delete($key), 'Key deletion failed');
        $this->assertFalse($this->cache->has($key), 'Key was found after deletion');
    }

    public function testDelete(): void
    {
        $key = time();
        $this->assertTrue($this->cache->set($key, 'bar'), 'Setting key failed');
        $this->assertTrue($this->cache->delete($key), 'Key deletion failed');
        $this->assertFalse($this->cache->has($key), 'Key was found after deletion');
    }
}
