<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;
use Throwable;

class Season extends Base
{
    public function getCreatedDateTime(string $timezone = 'America/New_York'): ?DateTime
    {
        if (!isset($this->extraArgs()['CreatedDateTime'])) {
            return null;
        }

        try {
            $createdDateTime = new DateTime($this->extraArgs()['CreatedDateTime'], new DateTimeZone($timezone));

            return $createdDateTime;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function getDescription(): string
    {
        return (string)$this->extraArgs()['Description'];
    }

    public function getEndDateTime(string $timezone = 'America/New_York'): ?DateTime
    {
        if (!isset($this->extraArgs()['EndDateTime'])) {
            return null;
        }

        try {
            $endDateTime = new DateTime($this->extraArgs()['EndDateTime'], new DateTimeZone($timezone));

            return $endDateTime;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function getId(): int
    {
        return intval($this->extraArgs()['Id']);
    }

    public function getStartDateTime(string $timezone = 'America/New_York'): ?\DateTime
    {
        if (!isset($this->extraArgs()['StartDateTime'])) {
            return null;
        }

        try {
            $startDateTime = new DateTime($this->extraArgs()['StartDateTime'], new DateTimeZone($timezone));

            return $startDateTime;
        } catch (Throwable $e) {
            return null;
        }
    }
}
