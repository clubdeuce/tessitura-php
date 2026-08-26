<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Resource;

class ElectronicAddressType extends Resource
{
    public function active(): bool
    {
        return !($this->extraArgs['Inactive'] ?? false);
    }

    public function isEmail(): bool
    {
        return (bool)($this->extraArgs['IsEmail'] ?? false);
    }

    /**
     * Returns the raw API response array, used when embedding this object
     * verbatim in a constituent creation POST body.
     *
     * @return mixed[]
     */
    public function rawResponse(): array
    {
        return $this->extraArgs;
    }
}
