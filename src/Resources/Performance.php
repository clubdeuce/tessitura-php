<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;

class Performance extends Base
{

    protected DateTime $_date;

    public function title(): string
    {
        return $this->_extra_args['PerformanceDescription'] ?? '';
    }

    /**
     * @throws \Exception
     */
    public function date(string $timezone = 'America/New_York'): ?\DateTime
    {
        if (isset($this->_date)) {
            return $this->_date;
        }

        if (isset($this->_extra_args['PerformanceDate'])) {
            try {
                return new DateTime($this->_extra_args['PerformanceDate'], new DateTimeZone($timezone));
            } catch (\Exception $e) {
                throw new \Exception("Unable to convert performance date into DateTime object: {$e->getMessage()}", E_USER_WARNING);
            }
        }

        return null;
    }

    public function productionSeasonId(): int
    {
        return $this->_extra_args['ProductionSeason']['Id'] ?? 0;
    }

    public function id(): int
    {
        return $this->_extra_args['PerformanceId'] ?? 0;
    }
}
