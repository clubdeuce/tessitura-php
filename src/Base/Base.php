<?php

namespace Clubdeuce\Tessitura\Base;

class Base {
    /**
     * Holds the state of the object.
     *
     * This property is used to store the values of the object's properties
     * that are set through the constructor or other methods.
     *
     * @var mixed[]
     */
    protected array $_extraArgs = [];

    /**
     * Base constructor.
     *
     * Initializes the object with the provided arguments.
     *
     * @param mixed[] $args
     */
    public function __construct(array $args = [])
    {
        $this->setState($args);
    }

    /**
     * Sets the state of the object based on the provided arguments.
     *
     * This method updates the object's properties based on the keys in the
     * provided array. If a key corresponds to a property that exists in the
     * object, it will be set. Otherwise, it will be stored in the `_extraArgs`
     * array for later use.
     *
     * @param mixed[] $args
     */
    protected function setState(array $args = []): void
    {
        foreach ($args as $key => $value) {
            $property = "_{$key}";

            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            } else {
                $this->_extraArgs[$key] = $value;
            }
        }
    }

    /**
     * Magic method to access properties dynamically.
     *
     * @param string $name
     * @param mixed[] $args
     * @return mixed
     */
    public function __call(string $name, array $args = []): mixed
    {
        $property = "_{$name}";

        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        if (isset($this->_extraArgs[$name])) {
            return $this->_extraArgs[$name];
        }

        return false;
    }

    /**
     * Parses the provided arguments and fills in defaults.
     *
     * @param mixed[] $args
     * @param mixed[] $defaults
     * @return mixed[]
     */
    public function parseArgs(array $args = [], array $defaults = []): array
    {
        foreach ($defaults as $key => $value) {
            if (!isset($args[$key])) {
                $args[$key] = $value;
            }
        }

        return $args;
    }

    /**
     * @return mixed[]
     */
    public function extraArgs(): array
    {
        return $this->_extraArgs;
    }
}