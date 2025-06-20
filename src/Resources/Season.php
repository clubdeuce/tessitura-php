<?php
namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;

class Season extends Base {

    public function getCreatedDateTime(string $timezone = 'America/New_York') : ?DateTime {

        // Check if 'CreatedDateTime' is present in the extra args
        if (!isset($this->extraArgs()['CreatedDateTime'])) {
            return null; // or throw an exception if required
        }

        try {
            $createdDateTime = new DateTime($this->extraArgs()['CreatedDateTime'], new DateTimeZone($timezone));

            return $createdDateTime;
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return null;
        }
    }

    public function getDescription(): string
    {
        return (string)$this->extraArgs()['Description'];
    }

    public function getEndDateTime(string $timezone = 'America/New_York') : ?DateTime
    {
        // Check if 'EndDateTime' is present in the extra args
        if (!isset($this->extraArgs()['EndDateTime'])) {
            return null;
        }

        try {
            $endDateTime = new DateTime($this->extraArgs()['EndDateTime'], new DateTimeZone($timezone));

            return $endDateTime;
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return null;
        }
    }

    public function getId(): int
    {
        return intval($this->extraArgs()['Id']);
    }

    public function getStartDateTime(string $timezone = 'America/New_York') : ?\DateTime
    {
        // Check if 'StartDateTime' is present in the extra args
        if (!isset($this->extraArgs()['StartDateTime'])) {
            return null; // or throw an exception if required
        }

        try {
            $startDateTime = new DateTime($this->extraArgs()['StartDateTime'], new DateTimeZone($timezone));

            return $startDateTime;
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return null;
        }
    }
}