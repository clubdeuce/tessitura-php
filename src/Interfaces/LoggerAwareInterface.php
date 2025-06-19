<?php

namespace Clubdeuce\Tessitura\Interfaces;

use Psr\Log\LoggerInterface;

/**
 * Interface LoggerAwareInterface
 * @package Clubdeuce\Tessitura\Interfaces
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger The logger instance to use.
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void;

    /**
     * Gets the logger instance.
     *
     * @return LoggerInterface|null The logger instance or null if none is set.
     */
    public function getLogger(): ?LoggerInterface;
}