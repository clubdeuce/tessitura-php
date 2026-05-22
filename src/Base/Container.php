<?php

namespace Clubdeuce\Tessitura\Base;

use Clubdeuce\Tessitura\Helpers;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;
use Clubdeuce\Tessitura\Resources;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Container
 * @package Clubdeuce\Tessitura
 *
 * A simple service container for managing dependencies.
 *
 * This class provides a lightweight dependency injection container.
 * It allows you to store and retrieve services and configuration parameters,
 * making it easier to manage dependencies and configuration throughout your application.
 *
 * Example usage:
 * <code>
 * $container = new Container(['db_host' => 'localhost']);
 * $container->set('logger', new Logger());
 * $logger = $container->get('logger');
 * $dbHost = $container->getParameter('db_host');
 * </code>
 */
class Container
{
    /**
     * @var array<string, string>
     */
    private const SERVICE_FACTORY_METHODS = [
        'http_client'              => 'createHttpClient',
        'logger'                   => 'createLogger',
        'api'                      => 'createApi',
        'performances'             => 'createPerformances',
        'sessions'                 => 'createSessions',
        'constituents'             => 'createConstituents',
        'constituent_types'        => 'createConstituentTypes',
        'original_sources'         => 'createOriginalSources',
        'electronic_address_types' => 'createElectronicAddressTypes',
        'address_types'            => 'createAddressTypes',
        'price_types'              => 'createPriceTypes',
        'product_keywords'         => 'createProductKeywords',
        'web_contents'             => 'createWebContents',
        'production_seasons'       => 'createProductionSeasons',
    ];

    /**
     * @var mixed[] Array of registered services.
     */
    private array $services = [];

    /**
     * @var mixed[] Array of configuration parameters.
     */
    private array $parameters;

    /**
     * Container constructor.
     *
     * @param mixed[] $parameters Configuration parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Get a service from the container.
     *
     * @param string $id The service ID
     * @return mixed The service instance
     * @throws \Exception If the service is not found
     */
    public function get(string $id): mixed
    {
        if (!isset($this->services[$id])) {
            $this->services[$id] = $this->createService($id);
        }

        return $this->services[$id];
    }

    /**
     * Set a service in the container.
     *
     * @param string $id The service ID
     * @param mixed $service The service instance
     * @return void
     */
    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }

    /**
     * Get a parameter from the container.
     *
     * @param string $name The parameter name
     * @param mixed $default The default value if the parameter is not found
     * @return mixed The parameter value
     */
    public function getParameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Set a parameter in the container.
     *
     * @param string $name The parameter name
     * @param mixed $value The parameter value
     * @return void
     */
    public function setParameter(string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Check if a service exists in the container.
     *
     * @param string $id The service ID
     * @return bool True if the service exists, false otherwise
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || array_key_exists($id, self::SERVICE_FACTORY_METHODS);
    }

    /**
     * Create a service.
     *
     * @param string $id The service ID
     * @return mixed The service instance
     * @throws \Exception If the service cannot be created
     */
    private function createService(string $id): mixed
    {
        $factory = self::SERVICE_FACTORY_METHODS[$id] ?? null;

        if (null === $factory) {
            throw new \Exception(sprintf('Service "%s" not found', $id));
        }

        return $this->{$factory}();
    }

    /**
     * Create an HTTP client.
     *
     * @return Client
     */
    private function createHttpClient(): Client
    {
        $baseRoute = $this->getParameter('base_route', '');

        return new Client([
            'base_uri' => $baseRoute,
            'timeout'  => $this->getParameter('timeout', 10.0),
        ]);
    }

    /**
     * Create a logger.
     *
     * @return LoggerInterface
     */
    private function createLogger(): LoggerInterface
    {
        // Use a custom logger if provided, otherwise use NullLogger
        return $this->getParameter('logger', new NullLogger());
    }

    /**
     * Create an API client.
     *
     * @return ApiInterface
     */
    private function createApi(): ApiInterface
    {
        $args = [
            'base_route' => $this->getParameter('base_route', ''),
            'machine'    => $this->getParameter('machine', ''),
            'password'   => $this->getParameter('password', ''),
            'usergroup'  => $this->getParameter('usergroup', ''),
            'username'   => $this->getParameter('username', ''),
            'version'    => $this->getParameter('version', '16'),
        ];

        return new Helpers\Api(
            $args,
            $this->get('http_client'),
            $this->get('logger'),
            $this->has('cache') ? $this->get('cache') : null
        );
    }

    /**
     * Create a Performances resource.
     *
     * @return ResourceInterface
     */
    private function createPerformances(): ResourceInterface
    {
        return new Resources\Performances(
            $this->get('api'),
            $this->get('product_keywords'),
            $this->get('price_types')
        );
    }

    private function createSessions(): ResourceInterface
    {
        return new Resources\Sessions($this->get('api'));
    }

    private function createConstituents(): ResourceInterface
    {
        return new Resources\Constituents(
            $this->get('api'),
            $this->get('original_sources'),
            $this->get('constituent_types'),
            $this->get('address_types'),
            $this->get('electronic_address_types')
        );
    }

    private function createConstituentTypes(): ResourceInterface
    {
        return new Resources\ConstituentTypes($this->get('api'));
    }

    private function createOriginalSources(): ResourceInterface
    {
        return new Resources\OriginalSources($this->get('api'));
    }

    private function createElectronicAddressTypes(): ResourceInterface
    {
        return new Resources\ElectronicAddressTypes($this->get('api'));
    }

    private function createAddressTypes(): ResourceInterface
    {
        return new Resources\AddressTypes($this->get('api'));
    }

    private function createPriceTypes(): ResourceInterface
    {
        return new Resources\PriceTypes($this->get('api'));
    }

    private function createProductKeywords(): ResourceInterface
    {
        return new Resources\ProductKeywords($this->get('api'));
    }

    private function createWebContents(): ResourceInterface
    {
        return new Resources\WebContents($this->get('api'));
    }

    private function createProductionSeasons(): ResourceInterface
    {
        return new Resources\ProductionSeasons($this->get('api'));
    }
}
