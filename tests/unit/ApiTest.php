<?php
namespace Clubdeuce\Tessitura\Tests;
use Clubdeuce\Tessitura\Helpers\Api;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Api::class)]
class ApiTest extends testCase {
    public function testGetUri() : void {
        $sut = new Api(['base_route' => 'https://api.tessitura.com/']);

        try {
            $reflection = new \ReflectionMethod($sut::class, '_get_uri');
            $result = $reflection->invoke($sut, 'test-endpoint');

            $this->assertEquals('https://api.tessitura.com/test-endpoint', $result);
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }

    public function testGetRequestArgs() : void {
        $sut = new Api();

        try{
            $reflection = new \ReflectionMethod($sut, '_get_request_args');
            $result = $reflection->invoke($sut);

            $this->assertIsArray($result, 'Api::_get_request_args returns an array');
            $this->assertArrayHasKey('cache_expiration', $result, 'Args contains cache_expiration key');
            $this->assertArrayHasKey('timeout', $result, 'Args contains timeout key');
            $this->assertArrayHasKey('headers', $result, 'Args contains headers key');
            $this->assertIsArray($result['headers'], 'Api::_get_request_args()["header"] is an array');
            $this->assertArrayHasKey('Authorization', $result['headers'], 'Api::_get_request_args Authorization header is set');
            $this->assertIsString($result['headers']['Authorization'], 'Api::_get_request_args Authorization header is a string');
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

    }

    public function testGetAuthorizationHeaderValue() : void {
        $sut = new Api();

        try {
            $reflection = new \ReflectionMethod($sut, '_get_authorization_header_value');
            $result = $reflection->invoke($sut);

            $this->assertIsString($result, 'Api::_get_authorization_header_value returns a string');
            $this->assertMatchesRegularExpression('/^Basic (.+)$/', $result);
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }
}