<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;

class Constituent extends Base
{
    public function getId(): int
    {
        return intval($this->extraArgs['Id'] ?? 0);
    }

    public function firstName(): string
    {
        return $this->extraArgs['FirstName'] ?? '';
    }

    public function lastName(): string
    {
        return $this->extraArgs['LastName'] ?? '';
    }

    /**
     * @return mixed[]
     */
    public function addresses(): array
    {
        return $this->extraArgs['Addresses'] ?? [];
    }

    /**
     * @return mixed[]
     */
    public function electronicAddresses(): array
    {
        return $this->extraArgs['ElectronicAddresses'] ?? [];
    }
}
