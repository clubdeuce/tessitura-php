<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;
use Exception;

class Performance extends Base
{
    protected DateTime $_date;

    public function title(): string
    {
        return $this->extraArgs['PerformanceDescription'] ?? '';
    }

    public function productionSeasonId(): int
    {
        return $this->extraArgs['ProductionSeason']['Id'] ?? 0;
    }

    public function id(): int
    {
        return intval($this->extraArgs['PerformanceId'] ?? 0);
    }

    public function description(): string
    {
        return (string)$this->extraArgs['PerformanceDescription'];
    }

    public function doorsOpen(): ?DateTime
    {
        if (!isset($this->extraArgs['DoorsOpen'])) {
            return null;
        }

        try {
            return new DateTime($this->extraArgs['DoorsOpen']);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_NOTICE);

            return null;
        }
    }

    public function facilityId(): int
    {
        if (!isset($this->extraArgs['Facility'])) {
            return 0;
        }

        if (!isset($this->extraArgs['Facility']['Id'])) {
            return 0;
        }

        return intval($this->extraArgs['Facility']['Id']);
    }

    public function facilityDescription(): string
    {
        if (!isset($this->extraArgs['Facility'])) {
            return '';
        }

        if (!isset($this->extraArgs['Facility']['Description'])) {
            return '';
        }

        return $this->extraArgs['Facility']['Description'];
    }

    public function startTime(): ?DateTime
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

        if (isset($this->extraArgs['PerformanceDate'])) {
            try {
                return new DateTime($this->extraArgs['PerformanceDate'], new DateTimeZone($timezone));
            } catch (Exception $e) {
                throw new Exception(
                    "Unable to convert performance date into DateTime object: {$e->getMessage()}",
                    E_USER_WARNING
                );
            }
        }

        return null;
    }

    public function statusId(): int
    {
        return intval($this->extraArgs['PerformanceStatus']['Id']);
    }
}
