<?php

namespace Clubdeuce\Tessitura\Interfaces;

use Psr\Log\LoggerInterface;

interface ApiInterface extends RequestHandlerInterface, ConfigurableApiInterface, ClientConfigurableInterface
{
    /**
     * @return string
     */
    public function baseRoute(): string;

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