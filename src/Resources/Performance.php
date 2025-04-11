<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class Performance extends Base
{

    protected DateTime $_date;

    public function title(): string
    {
        return $this->_extra_args['PerformanceDescription'] ?? '';
    }

    public function productionSeasonId(): int
    {
        return $this->_extra_args['ProductionSeason']['Id'] ?? 0;
    }

    public function id(): int
    {
        return intval($this->_extra_args['PerformanceId'] ?? 0);
    }

    /**
     * @return string
     */
    function buyTicketsUrl(): string
    {

        return sprintf('%1$s/%2$s/%3$s', Tessitura::settings()['tnew_url'], $this->production_season_id(), $this->performanceId());

    }

    function description(): string
    {

        return (string)$this->_extra_args['PerformanceDescription'];

    }

    function doorsOpen(): ?DateTime
    {

        try {
            return new DateTime($this->_extra_args['DoorsOpen']);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return null;
        }

    }

    function facilityId(): int
    {
        return intval($this->_extra_args['Facility']['Id']);
    }

    function facilityDescription(): string
    {
        return $this->_extra_args['Facility']['Description'];
    }

    function startTime(): ?DateTime
    {
        try {
            return $this->date();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    public function date(string $timezone = 'America/New_York'): ?DateTime
    {
        if (isset($this->_date)) {
            return $this->_date;
        }

        if (isset($this->_extra_args['PerformanceDate'])) {
            try {
                return new DateTime($this->_extra_args['PerformanceDate'], new DateTimeZone($timezone));
            } catch (Exception $e) {
                throw new Exception("Unable to convert performance date into DateTime object: {$e->getMessage()}", E_USER_WARNING);
            }
        }

        return null;
    }

    function statusId(): int
    {
        return intval($this->_extra_args['PerformanceStatus']['Id']);
    }

}
