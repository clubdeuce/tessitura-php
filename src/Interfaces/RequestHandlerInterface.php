<?php

namespace Clubdeuce\Tessitura\Interfaces;

use Exception;

interface RequestHandlerInterface
{
    /**
     * Make a GET request to the API.
     *
     * @param string $resource
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public function get(string $resource, array $args = []): mixed;

    /**
     * Make a POST request to the API.
     *
     * @param string $endpoint
     * @param array $args
     * @return array|Exception
     */
    public function post(string $endpoint, array $args = []): array|Exception;
}