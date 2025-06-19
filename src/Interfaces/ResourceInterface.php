<?php

namespace Clubdeuce\Tessitura\Interfaces;

/**
 * Interface ResourceInterface
 * @package Clubdeuce\Tessitura\Interfaces
 */
interface ResourceInterface
{
    /**
     * Constructor method for initializing the resource with dependencies.
     *
     * @param ApiInterface $api The API client to use for requests.
     * @return void
     */
    public function __construct(ApiInterface $api);
}