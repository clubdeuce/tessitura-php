<?php

namespace unit;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Base\Container;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;
use Clubdeuce\Tessitura\Resources\Performances;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(Container::class)]
#[UsesClass(Base::class)]
#[UsesClass(Api::class)]
#[UsesClass(Performances::class)]
class ContainerTest extends TestCase
{
    /**
     * Test creation of the http_client service.
     */
    public function testCreateServiceHttpClient(): void
    {
        $container  = new Container(['timeout' => 5.0, 'base_route' => 'https://example.com']);
        $httpClient = $container->get('http_client');

        $this->assertInstanceOf(Client::class, $httpClient);
        $this->assertSame(5.0, $httpClient->getConfig('timeout'));
    }

    /**
     * Test creation of the logger service.
     */
    public function testCreateServiceLogger(): void
    {
        $logger          = new \Monolog\Logger('test_logger');
        $container       = new Container(['logger' => $logger]);
        $retrievedLogger = $container->get('logger');

        $this->assertSame($logger, $retrievedLogger);
    }

    /**
     * Test creation of the api service.
     */
    public function testCreateServiceApi(): void
    {
        $parameters = [
            'base_route' => 'https://api.example.com',
            'machine'    => 'machine_name',
            'password'   => 'password123',
            'usergroup'  => 'group1',
            'username'   => 'user1',
            'version'    => '16',
        ];
        $container = new Container($parameters);
        $container->set('http_client', $this->createMock(Client::class));
        $container->set('logger', $this->createMock(LoggerInterface::class));

        $api = $container->get('api');

        $this->assertInstanceOf(ApiInterface::class, $api);
    }

    /**
     * Test creation of the performances service.
     */
    public function testCreateServicePerformances(): void
    {
        $mockApi   = $this->createMock(ApiInterface::class);
        $container = new Container();
        $container->set('api', $mockApi);

        $performances = $container->get('performances');

        $this->assertInstanceOf(ResourceInterface::class, $performances);
    }

    /**
     * Test that createService throws an exception for an invalid service ID.
     */
    public function testCreateServiceThrowsExceptionForInvalidService(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Service "invalid_service" not found');

        $container = new Container();
        $container->get('invalid_service');
    }

    /**
     *
     * @throws \Exception
     */
    public function testCreateHttpClientWithDefaultParameters(): void
    {
        $container  = new Container();
        $httpClient = $container->get('http_client');

        $this->assertInstanceOf(Client::class, $httpClient);
    }

    /**
     * @throws \Exception
     */
    public function testCreateHttpClientWithCustomTimeout(): void
    {
        $container = new Container(['timeout' => 5.0]);

        $httpClient = $container->get('http_client');

        $this->assertInstanceOf(Client::class, $httpClient);
        $this->assertSame(5.0, $httpClient->getConfig('timeout'));
    }

    /**
     * @throws \Exception
     */
    public function testGetExistingService(): void
    {
        $container = new Container();

        $service = $container->get('http_client');

        $this->assertInstanceOf(Client::class, $service);
    }

    /**
     * @throws \Exception
     */
    public function testSetOverwritesExistingService(): void
    {
        $container = new Container();

        $container->set('test_service', new \stdClass());
        $newService = new \DateTime();
        $container->set('test_service', $newService);

        $this->assertSame($newService, $container->get('test_service'));
    }

    /**
     * @throws \Exception
     */
    public function testSetStoresServiceSuccessfully(): void
    {
        $container = new Container();
        $service   = new \ArrayObject();

        $container->set('array_service', $service);

        $this->assertSame($service, $container->get('array_service'));
    }

    public function testGetNonExistentServiceThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Service "non_existent_service" not found');

        $container = new Container();
        $container->get('non_existent_service');
    }

    /**
     * @throws \Exception
     */
    public function testGetCustomSetService(): void
    {
        $customService = new \stdClass();
        $container     = new Container();
        $container->set('custom_service', $customService);

        $retrievedService = $container->get('custom_service');

        $this->assertSame($customService, $retrievedService);
    }

    /**
     * Test retrieving an existing parameter from the container.
     */
    public function testGetExistingParameter(): void
    {
        $container = new Container(['test_param' => 'value']);

        $this->assertSame('value', $container->getParameter('test_param'));
    }

    /**
     * Test retrieving a non-existent parameter with a default value.
     */
    public function testGetNonExistentParameterWithDefault(): void
    {
        $container = new Container();

        $this->assertSame('default_value', $container->getParameter('non_existent_param', 'default_value'));
    }

    /**
     * Test retrieving a non-existent parameter without a default value.
     */
    public function testGetNonExistentParameterWithoutDefault(): void
    {
        $container = new Container();

        $this->assertNull($container->getParameter('non_existent_param'));
    }

    /**
     * Test setting a parameter and retrieving it correctly.
     */
    public function testSetParameterSuccessfullyStoresParameter(): void
    {
        $container = new Container();
        $container->setParameter('new_param', 'new_value');

        $this->assertSame('new_value', $container->getParameter('new_param'));
    }

    /**
     * Test overwriting an existing parameter.
     */
    public function testSetParameterOverwritesExistingParameter(): void
    {
        $container = new Container(['existing_param' => 'old_value']);
        $container->setParameter('existing_param', 'new_value');

        $this->assertSame('new_value', $container->getParameter('existing_param'));
    }
}
