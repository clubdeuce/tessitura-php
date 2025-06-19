<?php

namespace Clubdeuce\Tessitura\Interfaces;

use GuzzleHttp\ClientInterface;

interface ClientConfigurableInterface
{
    /**
     * Set the HTTP client.
     *
     * @param ClientInterface $client
     * @return void
     */
    public function setClient(ClientInterface $client): void;

    /**
     * Get the HTTP client.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface;
}