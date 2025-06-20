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
class Base {
    protected array $_extraArgs = [];

    public function __construct(array $args = [])
    {
        $this->setState($args);
    }

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

    public function __call(string $name, array $args = []): mixed
    {
        // Add deprecation warning for magic method usage
        // This helps encourage developers to use explicit getter methods
        $commonMethods = ['id', 'name', 'description'];
        if (in_array(strtolower($name), $commonMethods)) {
            $methodName = 'get' . ucfirst($name);
            trigger_error(
                "Magic method $name() is deprecated. Use explicit method $methodName() instead for better IDE support and type safety.",
                E_USER_DEPRECATED
            );
        }

        $property = "_{$name}";

        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        if (isset($this->_extraArgs[$name])) {
            return $this->_extraArgs[$name];
        }

        return false;
    }

    public function parseArgs(array $args = [], array $defaults = []): array
    {
        foreach ($defaults as $key => $value) {
            if (!isset($args[$key])) {
                $args[$key] = $value;
            }
        }

        return $args;
    }

    public function extraArgs(): array
    {
        return $this->_extraArgs;
    }

    /**
     * Get a value from extraArgs by key
     *
     * @param string $key The key to get
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->_extraArgs[$key] ?? $default;
    }

    /**
     * Get ID value if it exists
     *
     * @return int The ID value or 0 if not found
     */
    public function getId(): int
    {
        return intval($this->get('Id', $this->get('id', 0)));
    }

    /**
     * Get name value if it exists
     *
     * @return string The name value or empty string if not found
     */
    public function getName(): string
    {
        return (string)$this->get('Name', $this->get('name', ''));
    }

    /**
     * Get description value if it exists
     *
     * @return string The description value or empty string if not found
     */
    public function getDescription(): string
    {
        return (string)$this->get('Description', $this->get('description', ''));
    }
}