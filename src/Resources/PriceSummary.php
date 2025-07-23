<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Resource;

class PriceSummary extends Resource
{
    public function price(): float
    {
        return floatval($this->extraArgs['Price']);
    }

    public function zoneId(): int
    {
        return intval($this->extraArgs['ZoneId']);
    }

    public function enabled(): bool
    {
        return (bool)$this->extraArgs['Enabled'];
    }

    public function performanceId(): int
    {
        return intval($this->extraArgs['PerformanceId']);
    }
}
