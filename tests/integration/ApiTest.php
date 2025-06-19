<?php

namespace Clubdeuce\Tessitura\Tests\Integration;

use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Api::class)]
class ApiTest extends TestCase
{
    public function testGet(): void
    {
        $response = new Response(200, [], file_get_contents(dirname(__DIR__) . '/fixtures/season.json'));
        $client = $this->createMock(Client::class);

        $client->method('get')->willReturn($response);

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/', 'client' => $client]);

        $response = $api->get('performances', []);

        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }

    public function testGet404Error(): void
    {
        $stream = new Stream(fopen(dirname(__DIR__) . '/fixtures/season.json', 'r'));
        $response = new Response(404, [], $stream);
        $client = $this->createMock(Client::class);

        $client->method('get')->willReturn($response);

        $api = new Api(['baseRoute' => 'https://api.tessitura.com/', 'client' => $client]);

        $this->expectException(\Exception::class);
        $api->get('performances', []);
    }
}