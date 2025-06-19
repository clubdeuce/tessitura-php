<?php
namespace Clubdeuce\Tessitura\Tests\Unit;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Tests\testCase;
use GuzzleHttp\Client;
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

    /**
     * Tests the logger functionality of the Api class.
     * It ensures that the proper logger is returned when set, and null is returned otherwise.
     */
    public function testLogger(): void
    {
        $sut = new Api([], $this->createMock(Client::class));

        // Test when logger is not set
        $this->assertNull($sut->getLogger(), 'getLogger should return null when no logger is set.');

        // Test when logger is set
        $mockLogger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $sut->setLogger($mockLogger);
        $this->assertSame($mockLogger, $sut->getLogger(), 'getLogger should return the assigned logger.');
    }

    /**
     * Tests the client functionality of the Api class.
     * It ensures that the client is correctly assigned and retrievable.
     * @throws Exception
     */
    public function testClient(): void
    {
        $mockClient = $this->createMock(Client::class);
        $sut = new Api([], $mockClient);

        $this->assertSame($mockClient, $sut->getClient(), 'Client was not set correctly.');
    }

    /**
     * Tests the setMachine functionality of the Api class.
     * Ensures that the machine value is correctly assigned and retrievable.
     * @throws Exception
     */
    public function testMachine(): void
    {
        $sut = new Api([], $this->createMock(Client::class));

        $machineName = 'TestMachine';
        $sut->setMachine($machineName);

        $this->assertSame($machineName, $sut->getMachine(), 'Machine was not set correctly.');
    }

    /**
     * Ensures that the base route value is correctly assigned and retrievable.
     * @throws Exception
     */
    public function testBaseRoute(): void
    {
        $baseRoute = 'https://api.example.com';
        $sut = new Api([], $this->createMock(Client::class));

        $sut->setBaseRoute($baseRoute);
        $this->assertSame($baseRoute, $sut->getBaseRoute(), 'Base route was not set correctly.');
    }

    /**
     * Ensures that the password is correctly assigned and retrievable.
     * @throws Exception
     */
    public function testSetPassword(): void
    {
        $password = 'TestPassword123';
        $sut = new Api([], $this->createMock(Client::class));

        $sut->setPassword($password);
        $this->assertSame($password, $sut->getPassword(), 'Password was not set correctly.');
    }

    /**
     * Ensures that the username is correctly assigned and retrievable.
     * @throws Exception
     */
    public function testSetUsername(): void
    {
        $username = 'TestUsername';
        $sut = new Api([], $this->createMock(Client::class));

        $sut->setUsername($username);
        $this->assertSame($username, $sut->getUsername(), 'Username was not set correctly.');
    }

    /**
     * Tests the setVersion functionality of the Api class.
     * @throws Exception
     */
    public function testSetVersion(): void
    {
        $sut = new Api([], $this->createMock(Client::class));
        
        $version = '17';
        $sut->setVersion($version);
        
        $this->assertSame($version, $sut->getVersion(), 'Version was not set correctly.');
    }

    /**
     * Tests the getUsergroup functionality of the Api class.
     * @throws Exception
     */
    public function testGetUsergroup(): void
    {
        $usergroup = 'TestUsergroup';
        $sut = new Api([
            'usergroup' => $usergroup
        ], $this->createMock(Client::class));
        
        $this->assertSame($usergroup, $sut->getUsergroup(), 'Usergroup getter did not return correct value.');
    }

    /**
     * Tests the setUsergroup functionality of the Api class.
     * @throws Exception
     */
    public function testSetUsergroup(): void
    {
        $sut = new Api([], $this->createMock(Client::class));
        
        $usergroup = 'NewUsergroup';
        $sut->setUsergroup($usergroup);
        
        $this->assertSame($usergroup, $sut->getUsergroup(), 'Usergroup was not set correctly.');
    }
}