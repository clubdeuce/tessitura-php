<?php

namespace Clubdeuce\Tessitura\Tests\Integration;

use Clubdeuce\Tessitura\Cache\ArrayCache;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Tests\testCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;

#[CoversClass(Api::class)]
#[CoversClass(ArrayCache::class)]
class ApiCacheIntegrationTest extends testCase
{
    /**
     * @throws Exception
     */
    public function testApiCacheIntegration(): void
    {
        // Create real cache instance
        $cache = new ArrayCache();
        
        // Create mock HTTP client that should only be called once
        $response = new Response(200, [], file_get_contents(dirname(__DIR__) . '/fixtures/season.json'));
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
               ->method('get')
               ->willReturn($response);

        // Create API instance with cache
        $api = new Api(
            ['baseRoute' => 'https://api.tessitura.com/'],
            $client,
            null,
            $cache
        );

        // Make the first request - should hit the HTTP client
        $result1 = $api->get('performances', []);
        $this->assertIsArray($result1);
        $this->assertNotEmpty($result1);

        // Make the second request - should hit the cache (HTTP client won't be called again)
        $result2 = $api->get('performances', []);
        $this->assertIsArray($result2);
        $this->assertEquals($result1, $result2);
    }

    /**
     * @throws Exception
     */
    public function testApiCacheWithCustomExpiration(): void
    {
        $cache = new ArrayCache();
        
        $response = new Response(200, [], '{"test": "data"}');
        $client = $this->createMock(Client::class);
        $client->method('get')->willReturn($response);

        $api = new Api(
            ['baseRoute' => 'https://api.tessitura.com/'],
            $client,
            null,
            $cache
        );

        // Make request with custom cache expiration
        $result = $api->get('test', ['cache_expiration' => 300]); // 5 minutes
        $this->assertIsArray($result);
        $this->assertEquals(['test' => 'data'], $result);
    }

    public function testApiCacheGettersSetters(): void
    {
        $cache = new ArrayCache();
        $api = new Api(['baseRoute' => 'https://api.tessitura.com/']);
        
        // Test cache setter and getter
        $this->assertNull($api->getCache());
        
        $api->setCache($cache);
        $this->assertSame($cache, $api->getCache());
    }
}