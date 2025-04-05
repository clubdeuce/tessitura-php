<?php
namespace Clubdeuce\Tessitura\Helpers;

use Clubdeuce\Tessitura\Base\Base;

/**
 * Class API
 * @package Clubdeuce\Tessitura\Helpers
 *
 * @method string base_route()
 * @method string machine()
 * @method string password()
 * @method string usergroup()
 * @method string username()
 * @method object logger()
 */
class Api extends Base {

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
     * API constructor.
     *
     * @param array $args {
     *      @type string $base_route
     *      @type string $location
     *      @type string $password
     *      @type string $usergroup
     *      @type string $username
     *      @type string $logger
     * }
     */
    public function __construct( array $args = [] ) {

        $args = $this->parse_args( $args, array(
            'base_route' => '',
            'machine'    => '',
            'password'   => '',
            'usergroup'  => '',
            'username'   => '',
            'logger'     => null,
            'version'    => '16',
        ) );

        $this->_set_state( $args );

    }

    /**
     * @param  string $resource
     * @param  array  $args
     *
     * @return array|mixed|\WP_Error
     */
    public function get( string $resource, array $args = array() ): mixed {

        $args = array_merge( $args, array(
            'method' => 'GET',
        ) );

        return $this->_make_request( $resource, $args );

    }

    /**
     * @param string $endpoint
     * @param array  $args
     *
     * @return array|\WP_Error
     */
    public function post( string $endpoint, array $args = [] ) {

        $args = array_merge( $args, array(
            'method' => 'POST',
        ) );

        return $this->_make_request( $endpoint, $args );

    }

    public function version(): string {
        // Different API versions have different URIs
        $paths = [
            '15' => '#^https://.*/TessituraService/?$#',
            '16' => '#^https://.*/tessitura/api/?$#'
        ];

        foreach($paths as $version => $pattern) {
            if(preg_match($pattern, $this->base_route())) {
                $this->_version = $version;
                break;
            }
        }

        return $this->_version;
    }

    /**
     * @param string $name
     * @param array $args
     *
     * @return null|mixed
     */
    public function __call( string $name, array $args = [] ) {

        $property_name = "_{$name}";

        if ( isset( $this->extra_args[ $name ] ) ) {
            return $this->extra_args[ $name ];
        }

        if ( property_exists( $this, $property_name ) ) {
            return $this->{$property_name};
        }

        return null;

    }

    /**
     * @param  string $endpoint
     *
     * @return string
     */
    protected function _get_uri( string $endpoint ) : string {

        return "{$this->base_route()}/{$endpoint}";

    }

    /**
     * @param  array $args
     *
     * @return array {
     * @type int   $cache_expiration
     * @type int   $timeout
     * @type array $headers
     * }
     */
    protected function _get_request_args( array $args = [] ) : array {

        $args = $this->parse_args( $args, array(
            'cache_expiration' => self::CACHE_EXPIRATION_DEFAULT,
            'timeout'          => 10,
            'headers'          => [],
            'body'             => '',
        ) );

        $parsed_url      = parse_url( $this->base_route() );
        $args['headers'] = $this->parse_args( $args['headers'], array(
            'Authorization'  => self::_get_authorization_header_value(),
            'Content-Type'   => 'application/json',
            'Content-Length' => $args['body'] ? strlen( json_encode( $args['body'] ) ) : 0,
            'Accept'         => 'application/json',
            'Host'           => $parsed_url['host'] ?? $this->base_route(),
        ) );

        return array_filter( $args );

    }

    /**
     * @return string
     */
    protected function _get_authorization_header_value (): string {

        $auth_key = sprintf( '%1$s:%2$s:%3$s:%4$s', $this->username(), $this->usergroup(), $this->machine(), $this->password() );

        return sprintf ( 'Basic %1$s', base64_encode( $auth_key ) );

    }

    /**
     * @param string $endpoint
     * @param array  $args
     *
     * @return array|mixed|\WP_Error
     */
    protected function _make_request( string $endpoint, array $args ) {

        $args = $this->_get_request_args( $args );

        $cache_expire = $args['cache_expiration'];
        $cache_key   = str_replace( '/', '-', $endpoint) . '_' . md5( $endpoint . json_encode( $args ) );
        $cache_group = 'tessitura';

        unset($args['cache_expiration']);

        do {
            if ( $response = wp_cache_get( $cache_key, $cache_group ) ) {
                $result = json_decode( wp_remote_retrieve_body( $response ),true );
                break;
            }

            $response = wp_remote_request( $this->_get_uri( $endpoint ), $args );

            if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
                wp_cache_set( $cache_key, $response, $cache_group, $cache_expire );
                $result = json_decode( wp_remote_retrieve_body( $response ),true );
                break;
            }

            // We have successfully gotten a response from the API, but not a 200 status code.
            if ( ! is_wp_error( $response ) ) {
                $result = new \WP_Error(
                    wp_remote_retrieve_response_code( $response),
                    wp_remote_retrieve_response_message( $response ),
                    wp_remote_retrieve_body( $response )
                );
                break;
            }

            $result = $response;
            trigger_error( $result->get_error_message() );
        } while ( false );

        return $result;

    }

    /**
     * @param string $message
     * @param array  $args {
     *      @type string $file
     *      @type string $line
     *      @type string $function
     *      @type array $trace
     *      @type mixed[] $extra
     * }
     */
    protected function _log_event( string $message, array $args = [] ) : void {

        $args = wp_parse_args( $args, array(
            'log'   => 'tessitura',
        ) );

        $message = 'Tessitura API: ' . $message;

        if ( $this->logger() ) {
            $this->logger()->log( $message, $args );
        }

    }

}
