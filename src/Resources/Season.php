<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Base\Resource;
use DateTime;
use DateTimeZone;
use Throwable;

class Season extends Resource
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

    public function setCreatedDateTime(string $createdDateTime): void
    {
        $this->extraArgs['CreatedDateTime'] = $createdDateTime;
    }

    public function getDescription(): string
    {
        return (string)$this->extraArgs()['Description'];
    }

    public function setDescription(string $description): void
    {
        $this->extraArgs['Description'] = $description;
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

    public function setStartDateTime(string $startDateTime): void
    {
        $this->extraArgs['StartDateTime'] = $startDateTime;
    }
}
