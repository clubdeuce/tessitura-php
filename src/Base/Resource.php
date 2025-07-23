<?php

namespace Clubdeuce\Tessitura\Base;

class Resource extends Base
{
    public function getId(): string
    {
        return $this->extraArgs['Id'] ?? '';
    }

    public function setId(string $id): void
    {
        $this->extraArgs['Id'] = $id;
    }

    public function getDescription(): string
    {
        return $this->extraArgs['Description'];
    }

    public function setDescription(string $description): void
    {
        $this->extraArgs['Description'] = $description;
    }
}
