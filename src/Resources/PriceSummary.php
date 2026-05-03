<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Resource;

class PriceSummary extends Resource
{
    public function price(): float
    {
        return floatval($this->extraArgs['Price'] ?? 0);
    }

    public function zoneId(): int
    {
        return intval($this->extraArgs['ZoneId'] ?? 0);
    }

    public function enabled(): bool
    {
        return (bool)($this->extraArgs['Enabled'] ?? false);
    }

    public function performanceId(): int
    {
        return intval($this->extraArgs['PerformanceId'] ?? 0);
    }
}
