<?php

namespace Clubdeuce\Tessitura\Interfaces;

use Psr\Log\LoggerInterface;

interface ApiInterface extends RequestHandlerInterface, ConfigurableApiInterface, ClientConfigurableInterface
{
    /**
     * @return string
     */
    public function getBaseRoute(): string;

    /**
     * @return string
     */
    public function getMachine(): string;

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @return string
     */
    public function getUsergroup(): string;

    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface;
}
