<?php
namespace Clubdeuce\Tessitura\Tests\integration;

use Clubdeuce\Tessitura\Cache\ArrayCache;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArrayCache::class)]
class ArrayCacheTest extends testCase
{
    protected ArrayCache $cache;

    public function setUp(): void
    {
        $this->cache = new ArrayCache();
    }
    public function testArrayCacheCanStoreAndRetrieveValues(): void
    {
        $this->assertTrue($this->cache->set('key1', 'value1'));
        $this->assertEquals('value1', $this->cache->get('key1'));

        $this->assertTrue($this->cache->set('key2', ['subkey' => 'subvalue']));
        $this->assertEquals(['subkey' => 'subvalue'], $this->cache->get('key2'));
    }

    public function testArrayCacheReturnsNullForNonExistentKeys(): void
    {
        $this->assertNull($this->cache->get('non_existent_key'));
    }

    public function testGetReturnsNullForExpiredKeys(): void
    {
        $this->cache->set('temp_key', 'temp_value', 1);

        sleep(2);

        $this->assertNull($this->cache->get('temp_key'));
    }

    public function testGetExpiredValueIsNull()
    {
        $this->cache->set('expired_key', 'expired_value', 1);
        $this->assertEquals('expired_value', $this->cache->get('expired_key'));
        sleep(2);
        $this->assertNull($this->cache->get('expired_key'));
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

    public function testClear(): void
    {
        $this->cache->set('key1', 'value1');
        $this->assertEquals('value1', $this->cache->get('key1'));
        $this->cache->clear();
        $this->assertFalse($this->cache->has('key1'), 'Cache key should be empty after clear');
        $this->assertNull($this->cache->get('key1'), 'Cache should return null after clear');
    }
}