<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Resource;

class PriceSummary extends Resource
{
    public function price(): float
    {

        return floatval($this->_extra_args['Price']);

    }

    public function zoneId() : int {

        return intval( $this->_extra_args['ZoneId'] );

    }
}
