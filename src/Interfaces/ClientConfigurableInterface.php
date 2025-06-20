<?php

namespace Clubdeuce\Tessitura\Interfaces;

use GuzzleHttp\Client;

interface ClientConfigurableInterface
{
    /**
     * Set the HTTP client.
     *
     * @param Client $client
     * @return void
     */
    public function setClient(Client $client): void;

    /**
     * Get the HTTP client.
     *
     * @return Client
     */
    public function getClient(): Client;
}
