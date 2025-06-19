<?php

namespace Clubdeuce\Tessitura\Interfaces;

interface ConfigurableApiInterface
{
    /**
     * Set the base route.
     *
     * @param string $baseRoute
     * @return void
     */
    public function setBaseRoute(string $baseRoute): void;

    /**
     * Set the machine name.
     *
     * @param string $machine
     * @return void
     */
    public function setMachine(string $machine): void;

    /**
     * Set the password.
     *
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void;

    /**
     * Set the username.
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void;

    /**
     * Set the usergroup.
     *
     * @param string $usergroup
     * @return void
     */
    public function setUsergroup(string $usergroup): void;

    /**
     * Set the API version.
     *
     * @param string $version
     * @return void
     */
    public function setVersion(string $version): void;
}