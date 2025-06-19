<?php
namespace Clubdeuce\Tessitura\Tests\Unit;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Tests\testCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;

#[CoversClass(Api::class)]
class ApiTest extends testCase {
    /**
     * @throws Exception
     */
    public function testGetUri() : void {
        $sut = new Api(['base_route' => 'https://api.tessitura.com'], $this->createMock(Client::class));

        $reflection = new \ReflectionMethod($sut::class, 'getUri');
        $result = $reflection->invoke($sut, 'test-endpoint');

        $this->assertEquals('https://api.tessitura.com/test-endpoint', $result);
    }

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    public function testGetRequestArgs() : void {
        $sut        = new Api([], $this->createMock(Client::class));
        $reflection = new \ReflectionMethod($sut, 'getRequestArgs');
        $result     = $reflection->invoke($sut);

        $this->assertIsArray($result, 'Api::getRequestArgs returns an array');
        $this->assertArrayHasKey('cache_expiration', $result, 'Args contains cache_expiration key');
        $this->assertArrayHasKey('timeout', $result, 'Args contains timeout key');
        $this->assertArrayHasKey('headers', $result, 'Args contains headers key');
        $this->assertIsArray($result['headers'], 'Api::getRequestArgs()["header"] is an array');
        $this->assertArrayHasKey('Authorization', $result['headers'], 'Api::getRequestArgs Authorization header is set');
        $this->assertIsString($result['headers']['Authorization'], 'Api::getRequestArgs Authorization header is a string');
    }

    public function testGetAuthorizationHeaderValue() : void {
        $sut        = new Api([], $this->createMock(Client::class));
        $reflection = new \ReflectionMethod($sut, 'getAuthorizationHeaderValue');;
        $result     = $reflection->invoke($sut);

        $this->assertIsString($result, 'Api::getAuthorizationHeaderValue returns a string');
        $this->assertMatchesRegularExpression('/^Basic (.+)$/', $result);
    }

//    public function testGetGuzzleError(): void {
//        try {
//            $client = $this->createMock(Client::class);
//            $client->method('get')->willThrowException(new ClientException('Sample Error',
//                new Request('get', 'https://api.tessitura.com/test-endpoint', [], ''),
//                new Response(404,  [],'Error')
//            ));
//
//            $sut = new Api([], $client);
//
//            $this->expectException(ClientException::class);
//            $sut->get('test-endpoint');
//        } catch (\Exception $e) {
//        } catch (Exception $e) {
//        }
//    }

}