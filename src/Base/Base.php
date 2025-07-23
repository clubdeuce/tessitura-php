<?php

namespace Clubdeuce\Tessitura\Base;

/**
 * Base class for Tessitura API resources.
 *
 * Provides a foundation for API resources with explicit getter methods
 * for better IDE support and type safety. Magic methods are still supported
 * for backward compatibility but are deprecated for common properties.
 *
 * @package Clubdeuce\Tessitura\Base
 */
class Base
{
    /**
     * Holds the state of the object.
     *
     * This property is used to store the values of the object's properties
     * that are set through the constructor or other methods.
     *
     * @var mixed[]
     */
    protected array $extraArgs = [];

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

                continue;
            }

            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }

            $this->extraArgs[$key] = $value;
        }
    }

    /**
     * Parses the provided arguments and fills in defaults.
     *
     * For each key in $defaults that is not present in $args, the default value is added to $args.
     *
     * @param mixed[] $args     The arguments to parse.
     * @param mixed[] $defaults The default values to use for missing arguments.
     * @return mixed[] The resulting array with defaults filled in.
     */
    public function parseArgs(array $args = [], array $defaults = []): array
    {
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $args)) {
                $args[$key] = $value;
            }
        }

        return $args;
    }

    /**
     * Returns all extra arguments as an associative array.
     *
     * @return mixed[] The array of extra arguments.
     */
    public function extraArgs(): array
    {
        return $this->extraArgs;
    }
}
