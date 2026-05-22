<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Resource;

class PriceType extends Resource
{
    public function active(): bool
    {
        return !($this->extraArgs['Inactive'] ?? false);
    }

    public function shortDescription(): string
    {
        return $this->extraArgs['ShortDescription'] ?? '';
    }
}
