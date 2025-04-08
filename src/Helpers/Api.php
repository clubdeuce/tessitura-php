<?php

namespace Clubdeuce\Tessitura\Helpers;

use Clubdeuce\Tessitura\Base\Base;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;

/**
 * Class API
 * @package Clubdeuce\Tessitura\Helpers
 *
 * @method string base_route()
 * @method string machine()
 * @method string password()
 * @method string usergroup()
 * @method string username()
 * @method object|null logger()
 */
class Api extends Base
{

    const CACHE_EXPIRATION_DEFAULT = 10 * 60; // 10 minutes

    /**
     * @var string The base path to the Tessitura API
     */
    protected string $_base_route;

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
     * @var Client GuzzleHttp client
     */
    protected Client $_client;

    /**
     * API constructor.
     *
     * @param mixed[] $args {
     * @type string   $base_route
     * @type string   $location
     * @type string   $password
     * @type string   $usergroup
     * @type string   $username
     * @type string   $logger
     * }
     */
    public function __construct(array $args = [])
    {

        $args = $this->parse_args($args, array(
            'base_route' => '',
            'machine' => '',
            'password' => '',
            'usergroup' => '',
            'username' => '',
            'logger' => null,
            'version' => '16',
            'client' => null,
        ));

        if (!isset($args['client'])) {
            $args['client'] = new Client([
                'base_uri' => $args['base_route'],
                'timeout' => 10.0,
            ]);
        }

        $this->_set_state($args);

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

        return $this->_make_request($resource, $args);
    }

    /**
     * @param string  $endpoint
     * @param mixed[] $args
     * @return mixed
     * @throws Exception
     */
    protected function _make_request(string $endpoint, array $args)
    {

        $args = $this->_get_request_args($args);

        $cache_expire = $args['cache_expiration'];
        $cache_key = str_replace('/', '-', $endpoint) . '_' . md5($endpoint . json_encode($args));
        $cache_group = 'tessitura';

        unset($args['cache_expiration']);

        try {
            /**
             * @var Response $response
             */
            $response = $this->_client->get($this->_get_uri($endpoint), $args);

            if (200 === $response->getStatusCode()) {
//                wp_cache_set($cache_key, $response, $cache_group, $cache_expire);
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
        } catch (GuzzleException $e) {

        }

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
    protected function _get_request_args(array $args = []): array
    {

        $args = $this->parse_args($args, array(
            'cache_expiration' => self::CACHE_EXPIRATION_DEFAULT,
            'headers' => [],
            'body' => '',
        ));

        $parsed_url = parse_url($this->base_route());
        $body = $args['body'] ?? json_encode($args['body']);
        $args['headers'] = $this->parse_args($args['headers'], array(
            'Authorization' => $this->_get_authorization_header_value(),
            'Content-Type' => 'application/json',
            'Content-Length' => $body ? strlen($body) : 0,
            'Accept' => 'application/json',
            'Host' => $parsed_url['host'] ?? $this->base_route(),
        ));

        return array_filter($args);

    }

    /**
     * @return string
     */
    protected function _get_authorization_header_value(): string
    {

        $auth_key = sprintf('%1$s:%2$s:%3$s:%4$s', $this->username(), $this->usergroup(), $this->machine(), $this->password());

        return sprintf('Basic %1$s', base64_encode($auth_key));

    }

    /**
     * @param string $endpoint
     *
     * @return string
     */
    protected function _get_uri(string $endpoint): string
    {

        return "{$this->base_route()}/{$endpoint}";

    }

    /**
     * @param string $endpoint
     * @param mixed[] $args
     * @return Exception|mixed[]
     */
    public function post(string $endpoint, array $args = []): array|Exception
    {

        $args = array_merge($args, array(
            'method' => 'POST',
        ));

        return $this->_make_request($endpoint, $args);

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
    protected function _log_event(string $message, array $args = []): void
    {

        $args = $this->parse_args($args, array(
            'log' => 'tessitura',
        ));

        $message = 'Tessitura API: ' . $message;

        if ($this->logger()) {
            $this->logger()->log($message, $args);
        }

    }

}
