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
 */
class Container
{
    /**
     * @var mixed[]
     */
    private array $services = [];

    /**
     * @var mixed[]
     */
    private array $parameters = [];

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
        return isset($this->services[$id]) || method_exists($this, 'create' . ucfirst($id));
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
        switch ($id) {
            case 'http_client':
                return $this->createHttpClient();
            case 'logger':
                return $this->createLogger();
            case 'api':
                return $this->createApi();
            case 'performances':
                return $this->createPerformances();
            default:
                throw new \Exception(sprintf('Service "%s" not found', $id));
        }
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
            'baseRoute' => $baseRoute,
            'timeout'   => $this->getParameter('timeout', 10.0),
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
        return new Resources\Performances($this->get('api'));
    }
}
