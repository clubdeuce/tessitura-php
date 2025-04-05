<?php
namespace Clubdeuce\Tessitura\Tests\Integration;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Tests\testCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream;
use http\Client\Response;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Api::class)]
class ApiTest extends testCase {
    public function testGet(): void {
        $response = new \GuzzleHttp\Psr7\Response(200, [], file_get_contents(dirname(__DIR__) . '/fixtures/season.json'));
        $client   = $this->createMock(Client::class);

        $client->method('get')->willReturn($response);

        $sut = new Api(['base_route' => 'https://api.tessitura.com/', 'client' => $client]);

        $response = $sut->get('performances', []);

        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }

    public function testGetError(): void {
        $stream = new Stream(fopen(dirname(__DIR__) . '/fixtures/season.json', 'r'));
        $response = new \GuzzleHttp\Psr7\Response(404, [], $stream);
        $client   = $this->createMock(Client::class);

        $client->method('get')->willReturn($response);

        $sut = new Api(['base_route' => 'https://api.tessitura.com/', 'client' => $client]);

        $this->expectException(\Exception::class);
        $response = $sut->get('performances', []);
    }
}