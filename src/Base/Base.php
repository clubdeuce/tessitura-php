<?php

namespace Clubdeuce\Tessitura\Base;

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
}