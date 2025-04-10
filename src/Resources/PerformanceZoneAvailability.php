<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Resource;

class PerformanceZoneAvailability extends Resource {
    /**
     * @var int
     */
    protected int $_availableCount = 0;

    protected int $_performanceId = 0;

    /**
     * @var null|string[]
     */
    protected array $_zone;

    /**
     * @return int
     */
    public function availableCount() : int {

        return intval($this->_availableCount);

    }

    /**
     * @return object
     */
    public function zone() : object {

        $zone = new \stdClass();

        $zone->id               = intval($this->_zone['Id']);
        $zone->description      = $this->_zone['Description'];
        $zone->shortDescription = $this->_zone['ShortDescription'];
        $zone->rank             = $this->_zone['Rank'];
        $zone->zoneMapId        = $this->_zone['ZoneMapId'];
        $zone->zoneTime         = $this->_zone['ZoneTime'];
        $zone->abbreviation     = $this->_zone['Abbreviation'];
        $zone->zoneLegend       = $this->_zone['ZoneLegend'];
        $zone->zoneGroup        = $this->_zone['ZoneGroup'];

        return $zone;

    }

}