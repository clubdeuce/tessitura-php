<?php

namespace Clubdeuce\Tessitura\Interfaces;

use Exception;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
 * Interface ApiInterface
 * @package Clubdeuce\Tessitura\Interfaces
 */
interface ApiInterface
{
    /**
     * @param string $resource
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public function get(string $resource, array $args = []): mixed;

    /**
     * @param string $endpoint
     * @param array $args
     * @return array|Exception
     */
    public function post(string $endpoint, array $args = []): array|Exception;

    /**
     * @return string
     */
    public function base_route(): string;

    /**
     * @return string
     */
    public function machine(): string;

    /**
     * @return string
     */
    public function password(): string;

    /**
     * @return string
     */
    public function usergroup(): string;

    /**
     * @return string
     */
    public function username(): string;

    /**
     * @return LoggerInterface|null
     */
    public function logger(): ?LoggerInterface;
}