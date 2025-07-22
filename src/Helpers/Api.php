<?php

namespace Clubdeuce\Tessitura\Helpers;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\CacheInterface;
use Clubdeuce\Tessitura\Interfaces\LoggerAwareInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use Psr\Log\LoggerInterface;

/**
 * Class API
 * @package Clubdeuce\Tessitura\Helpers
 */
class Api extends Base implements
    ApiInterface,
    LoggerAwareInterface
{
    protected const CACHE_EXPIRATION_DEFAULT = 10 * 60; // 10 minutes

    // These are the parameters that are required to connect to the Tessitura API.
    protected string $_base_route = '';

    protected string $_machine;

    protected string $_password;

    protected string $_username;

    /**
     * @var string The usergroup required for authentication
     */
    protected string $_usergroup;

    /**
     * @var string The Tessitura API version to use with this library
     */
    protected string $_version = '15';

    /**
     * @var Client GuzzleHttp client
     */
    protected Client $_client;

    /**
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $_logger = null;

    /**
     * @var CacheInterface|null
     */
    protected ?CacheInterface $_cache = null;

    /**
     * API constructor.
     *
     * @param mixed[] $args {
     * @type string   $base_route
     * @type string   $location
     * @type string   $password
     * @type string   $usergroup
     * @type string   $username
     * @type string   $version
     * }
     * @param Client|null $client The HTTP client to use for API requests
     * @param LoggerInterface|null $logger The logger to use for logging
     * @param CacheInterface|null $cache The cache implementation to use for caching API responses
     */
    public function __construct(
        array $args = [],
        ?Client $client = null,
        ?LoggerInterface $logger = null,
        ?CacheInterface $cache = null
    ) {
        $args = $this->parseArgs($args, [
            'baseRoute' => '',
            'machine'   => '',
            'password'  => '',
            'usergroup' => '',
            'username'  => '',
            'version'   => '16',
        ]);

        if ($logger) {
            $this->setLogger($logger);
        }

        if ($cache) {
            $this->setCache($cache);
        }

        if (!$client && !empty($args['baseRoute'])) {
            $client = new Client([
                'base_uri' => $args['baseRoute'],
                'timeout'  => 10.0,
            ]);
        }

        $args['client'] = $client;

        parent::__construct($args);
    }

    /**
     * @param string $resource
     * @param mixed[] $args
     * @return mixed
     * @throws Exception
     */
    public function get(string $resource, array $args = []): mixed
    {
        $args = array_merge($args, [
            'method' => 'GET',
        ]);

        return $this->makeRequest($resource, $args);
    }

    /**
     * @param string  $endpoint
     * @param mixed[] $args
     * @return mixed[]
     * @throws Exception|GuzzleException
     */
    protected function makeRequest(string $endpoint, array $args): array
    {
        $args = $this->parseArgs($args, [
            'method' => 'GET',
        ]);

        $method   = $args['method'];
        $cacheKey = $this->generateCacheKey($endpoint, $args);

        // Only cache GET requests
        if ($method === 'GET' && $this->_cache) {
            $cachedResponse = $this->_cache->get($cacheKey);

            if ($cachedResponse !== null) {
                $this->logEvent('Cache hit for endpoint: ' . $endpoint);

                return $cachedResponse;
            }
        }

        // Use the appropriate HTTP method
        if ($method === 'POST') {
            $response = $this->_client->post($this->getUri($endpoint), $args);
        } else {
            $response = $this->_client->get($this->getUri($endpoint), $args);
        }

        if (200 === $response->getStatusCode()) {
            $data = json_decode($response->getBody(), true);

            // Cache successful GET responses
            if ($method === 'GET' && $this->_cache) {
                $cacheExpiration = $args['cache_expiration'] ?? self::CACHE_EXPIRATION_DEFAULT;
                $this->_cache->set($cacheKey, $data, $cacheExpiration);
                $this->logEvent('Cached response for endpoint: ' . $endpoint);
            }

            return $data;
        }

        // We have successfully gotten a response from the API, but not a 200 status code.
        /**
         * @var Stream $body
         */
        $body = $response->getBody();

        $this->logEvent("Error response from endpoint: {$endpoint}. {$body->getContents()}");

        throw new Exception(
            $body->getContents(),
            $response->getStatusCode()
        );
    }

    public function getVersion(): string
    {
        return $this->_version;
    }

    /**
     * @param mixed[] $args
     *
     * @return mixed[] {
     * @type int $cache_expiration
     * @type int $timeout
     * @type array $headers
     * }
     */
    protected function getRequestArgs(array $args = []): array
    {

        $args = $this->parseArgs($args, [
            'cache_expiration' => self::CACHE_EXPIRATION_DEFAULT,
            'headers'          => [],
            'body'             => '',
            'timeout'          => 10.0,
        ]);

        if (is_array($args['body'])) {
            if (empty($args['body'])) {
                $args['body'] = null;
            } else {
                $args['body'] = json_encode($args['body']);
            }
        }

        $parsedUrl       = parse_url($this->baseRoute());
        $args['headers'] = $this->parseArgs($args['headers'], [
            'Authorization'  => $this->getAuthorizationHeaderValue(),
            'Content-Type'   => 'application/json',
            'Content-Length' => strlen($args['body']),
            'Accept'         => 'application/json',
            'Host'           => $parsedUrl['host'] ?? $this->baseRoute(),
        ]);

        return array_filter($args);
    }

    /**
     * @return string
     */
    protected function getAuthorizationHeaderValue(): string
    {

        $auth_key = sprintf(
            '%1$s:%2$s:%3$s:%4$s',
            $this->getUsername(),
            $this->getUsergroup(),
            $this->getMachine(),
            $this->getPassword()
        );

        return sprintf('Basic %1$s', base64_encode($auth_key));
    }

    /**
     * @param string $endpoint
     *
     * @return string
     */
    protected function getUri(string $endpoint): string
    {

        return "{$this->baseRoute()}/{$endpoint}";
    }

    /**
     * @param string $endpoint
     * @param mixed[] $args
     * @return Exception|mixed[]
     * @throws GuzzleException
     */
    public function post(string $endpoint, array $args = []): array|Exception
    {

        $args = array_merge($args, [
            'method' => 'POST',
        ]);

        return $this->makeRequest($endpoint, $args);
    }

    /**
     * @param string $message
     * @param mixed[] $args {
     * @type string $file
     * @type string $line
     * @type string $function
     * @type array $trace
     * @type mixed[] $extra
     * }
     */
    protected function logEvent(string $message, array $args = []): void
    {

        $args = $this->parseArgs($args, [
            'log' => 'tessitura',
        ]);

        $message = 'Tessitura API: ' . $message;

        if ($this->getLogger()) {
            $this->getLogger()->info($message, $args);
        }
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger The logger instance to use.
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->_logger = $logger;
    }

    /**
     * Gets the logger instance.
     *
     * @return LoggerInterface|null The logger instance or null if none is set.
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->_logger;
    }

    public function logger(): ?LoggerInterface
    {
        return $this->getLogger();
    }

    public function setClient(Client $client): void
    {
        $this->_client = $client;
    }

    public function getClient(): Client
    {
        return $this->_client;
    }

    public function baseRoute(): string
    {
        return $this->_base_route;
    }

    public function setBaseRoute(string $baseRoute): void
    {
        $this->_base_route = $baseRoute;
    }

    public function setMachine(string $machine): void
    {
        $this->_machine = $machine;
    }

    public function setPassword(string $password): void
    {
        $this->_password = $password;
    }

    public function setUsername(string $username): void
    {
        $this->_username = $username;
    }

    public function setUsergroup(string $usergroup): void
    {
        $this->_usergroup = $usergroup;
    }

    public function setVersion(string $version): void
    {
        $this->_version = $version;
    }

    public function getBaseRoute(): string
    {
        return $this->_base_route;
    }

    public function getMachine(): string
    {
        return $this->_machine;
    }

    public function getPassword(): string
    {
        return $this->_password;
    }

    public function getUsergroup(): string
    {
        return $this->_usergroup;
    }

    public function getUsername(): string
    {
        return $this->_username;
    }

    /**
     * Sets a cache instance on the object.
     *
     * @param CacheInterface $cache The cache instance to use.
     * @return void
     */
    public function setCache(CacheInterface $cache): void
    {
        $this->_cache = $cache;
    }

    /**
     * Gets the cache instance.
     *
     * @return CacheInterface|null The cache instance or null if none is set.
     */
    public function getCache(): ?CacheInterface
    {
        return $this->_cache;
    }

    /**
     * Generate a cache key for the given endpoint and arguments.
     *
     * @param string   $endpoint The API endpoint
     * @param string[] $args The request arguments
     * @return string  The generated cache key
     */
    protected function generateCacheKey(string $endpoint, array $args): string
    {
        // Remove method and cache-specific args from key generation
        $keyArgs = $args;
        unset($keyArgs['method'], $keyArgs['cache_expiration']);

        // Create a consistent key based on endpoint and args
        $keyData = [
            'endpoint'   => $endpoint,
            'base_route' => $this->baseRoute(),
            'version'    => $this->getVersion(),
            'args'       => $keyArgs,
        ];

        return 'tessitura:' . md5(json_encode($keyData));
    }
}
