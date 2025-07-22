<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Resource;

class PriceSummary extends Resource
{
    public function price(): float
    {
        return floatval($this->_extraArgs['Price']);
    }

    public function zoneId(): int
    {
        return intval($this->_extraArgs['ZoneId']);
    }

    public function enabled(): bool
    {
        return (bool)$this->_extraArgs['Enabled'];
    }

    public function performanceId(): int
    {
        return intval($this->_extraArgs['PerformanceId']);
    }
}
