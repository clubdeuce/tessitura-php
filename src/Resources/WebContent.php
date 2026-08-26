<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;

class WebContent extends Base
{
    public function getId(): ?int
    {
        return isset($this->extraArgs['Id']) ? intval($this->extraArgs['Id']) : null;
    }

    public function typeId(): int
    {
        return intval($this->extraArgs['Type']['Id'] ?? 0);
    }

    public function content(): string
    {
        return (string)($this->extraArgs['Value'] ?? '');
    }
}
