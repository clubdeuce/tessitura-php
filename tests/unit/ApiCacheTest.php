<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Cache\RedisCache;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Interfaces\CacheInterface;
use Clubdeuce\Tessitura\Tests\testCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;

#[CoversClass(Api::class)]
#[CoversClass(RedisCache::class)]
class ApiCacheTest extends testCase
{
    /**
     * @throws Exception
     */
    public function testCacheSetAndGet(): void
    {
        // Create a mock cache implementation
        $cache = $this->createMock(CacheInterface::class);

        // Set up the cache to return null initially (cache miss)
        $cache->expects($this->once())
              ->method('get')
              ->willReturn(null);

        // Expect cache->set to be called once
        $cache->expects($this->once())
              ->method('set')
              ->willReturn(true);

        // Create mock HTTP client
        $response = new Response(200, [], file_get_contents(dirname(__DIR__) . '/fixtures/season.json'));
        $client   = $this->createMock(Client::class);
        $client->method('get')->willReturn($response);

        // Create API instance with cache
        $api = new Api(
            ['baseRoute' => 'https://api.tessitura.com/'],
            $client,
            null,
            $cache
        );

        // Make the request - should hit cache miss and then set cache
        $result = $api->get('performances', []);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testCacheHit(): void
    {
        $cachedData = ['cached' => 'response'];

        // Create a mock cache that returns cached data
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
              ->method('get')
              ->willReturn($cachedData);

        // Create mock HTTP client that should never be called
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('get');

        // Create API instance with cache
        $api = new Api(
            ['baseRoute' => 'https://api.tessitura.com/'],
            $client,
            null,
            $cache
        );

        // Make the request - should return cached data
        $result = $api->get('performances', []);

        $this->assertEquals($cachedData, $result);
    }

    /**
     * @throws Exception
     */
    public function testPostRequestsNotCached(): void
    {
        // Create a mock cache that should never be called
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->never())->method('get');
        $cache->expects($this->never())->method('set');

        // Create mock HTTP client
        $response = new Response(200, [], '{"success": true}');
        $client   = $this->createMock(Client::class);
        $client->method('post')->willReturn($response);

        // Create API instance with cache
        $api = new Api(
            ['baseRoute' => 'https://api.tessitura.com/'],
            $client,
            null,
            $cache
        );

        // Make a POST request - should not use cache
        $result = $api->post('performances', []);

        $this->assertIsArray($result);
    }

    public function testCacheKeyGeneration(): void
    {
        $api = new Api(['baseRoute' => 'https://api.tessitura.com/']);

        $reflection = new \ReflectionClass($api);
        $method     = $reflection->getMethod('generateCacheKey');
        $method->setAccessible(true);

        // Test with basic endpoint
        $key1 = $method->invokeArgs($api, ['endpoint1', ['method' => 'GET']]);
        $key2 = $method->invokeArgs($api, ['endpoint2', ['method' => 'GET']]);

        $this->assertNotEquals($key1, $key2);
        $this->assertStringStartsWith('tessitura:', $key1);
        $this->assertStringStartsWith('tessitura:', $key2);

        // Test with same endpoint and args should produce same key
        $key3 = $method->invokeArgs($api, ['endpoint1', ['method' => 'GET']]);
        $this->assertEquals($key1, $key3);
    }
}
