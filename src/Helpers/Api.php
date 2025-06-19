<?php

namespace Clubdeuce\Tessitura\Helpers;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\LoggerAwareInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
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

    const CACHE_EXPIRATION_DEFAULT = 10 * 60; // 10 minutes

    /**
     * @var string The base path to the Tessitura API
     */
    protected string $_base_route = '';

    /**
     * @var string The machine name required for authentication.
     */
    protected string $_machine;

    /**
     * @var string The password required for authentication.
     */
    protected string $_password;

    /**
     * @var string The username required for authentication.
     */
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
     * @var ClientInterface GuzzleHttp client
     */
    protected ClientInterface $_client;

    /**
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $_logger = null;

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
     * @param ClientInterface|null $client The HTTP client to use for API requests
     * @param LoggerInterface|null $logger The logger to use for logging
     */
    public function __construct(
        array $args = [],
        ?ClientInterface $client = null,
        ?LoggerInterface $logger = null
    ) {
        $args = $this->parseArgs($args, array(
            'baseRoute' => '',
            'machine' => '',
            'password' => '',
            'usergroup' => '',
            'username' => '',
            'version' => '16',
        ));

        if ($logger) {
            $this->setLogger($logger);
        }

        if (!$client && !empty($args['baseRoute'])) {
            $client = new Client([
                'base_uri' => $args['baseRoute'],
                'timeout' => 10.0,
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
    public function get(string $resource, array $args = array()): mixed
    {
        $args = array_merge($args, array(
            'method' => 'GET',
        ));

        return $this->makeRequest($resource, $args);
    }

    /**
     * @param string  $endpoint
     * @param mixed[] $args
     * @return mixed
     * @throws Exception|GuzzleException
     */
    protected function makeRequest(string $endpoint, array $args): array
    {
        /**
         * @var Response $response
         */
        $response = $this->_client->get($this->getUri($endpoint), $args);

        if (200 === $response->getStatusCode()) {
            return json_decode($response->getBody(), true);
        }

        // We have successfully gotten a response from the API, but not a 200 status code.
        /**
         * @var Stream $body
         */
        $body = $response->getBody();

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

        $args = $this->parseArgs($args, array(
            'cache_expiration' => self::CACHE_EXPIRATION_DEFAULT,
            'headers'          => [],
            'body'             => '',
            'timeout'          => 10.0,
        ));

        if (is_array($args['body'])) {
            if (empty($args['body'])) {
                $args['body'] = null;
            } else {
                $args['body'] = json_encode($args['body']);
            }
        }

        $parsedUrl = parse_url($this->baseRoute());
        $args['headers'] = $this->parseArgs($args['headers'], array(
            'Authorization' => $this->getAuthorizationHeaderValue(),
            'Content-Type'   => 'application/json',
            'Content-Length' => strlen($args['body']),
            'Accept'         => 'application/json',
            'Host'           => $parsedUrl['host'] ?? $this->baseRoute(),
        ));

        return array_filter($args);

    }

    /**
     * @return string
     */
    protected function getAuthorizationHeaderValue(): string
    {

        $auth_key = sprintf('%1$s:%2$s:%3$s:%4$s', $this->getUsername(), $this->getUsergroup(), $this->getMachine(), $this->getPassword());

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

        $args = array_merge($args, array(
            'method' => 'POST',
        ));

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

        $args = $this->parseArgs($args, array(
            'log' => 'tessitura',
        ));

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

    public function setClient(ClientInterface $client): void
    {
        $this->_client = $client;
    }

    public function getClient(): ClientInterface
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
}
