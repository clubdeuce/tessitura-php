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
     * Returns a DateTime for this performance in the requested timezone.
     *
     * Each call returns an independent object, so requesting different timezones
     * always produces correct, timezone-aware values regardless of call order.
     *
     * @throws Exception
     */
    public function date(string $timezone = 'America/New_York'): ?DateTime
    {
        if (isset($this->_date)) {
            try {
                $clone = clone $this->_date;
                $clone->setTimezone(new DateTimeZone($timezone));

                return $clone;
            } catch (Exception $e) {
                throw new Exception(
                    "Unable to convert performance date into DateTime object: {$e->getMessage()}",
                    E_USER_WARNING
                );
            }
        }

        if (isset($this->extraArgs['PerformanceDate'])) {
            try {
                // Pass $timezone so that date strings without an embedded offset are
                // interpreted in the requested timezone.  For ISO-8601 strings that
                // already carry an offset the constructor ignores the parameter, so
                // setTimezone() is required to perform the actual conversion.
                $date = new DateTime($this->extraArgs['PerformanceDate'], new DateTimeZone($timezone));
                $date->setTimezone(new DateTimeZone($timezone));

                return $date;
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
        return intval($this->extraArgs['PerformanceStatus']['Id'] ?? 0);
    }
}
