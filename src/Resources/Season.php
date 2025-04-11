<?php
namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;

class Season extends Base {

    public function createdDateTime(string $timezone = 'America/New_York') : ?DateTime {
        try {
            $timezone = new DateTimeZone($timezone);

            if(isset($this->extra_args()['CreatedDateTime'])) {
                try {
                    return new DateTime($this->extra_args()['CreatedDateTime'], $timezone);
                } catch (\Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
            }
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
        }

        return null;
    }

    public function description(): string
    {
        return (string)$this->extra_args()['Description'];
    }

    public function endDateTime(string $timezone = 'America/New_York') : ?DateTime
    {
        try {
            $timezone = new DateTimeZone($timezone);

            if(isset( $this->extra_args()['EndDateTime'])) {
                try {
                    return new \DateTime( $this->extra_args()['EndDateTime'], $timezone );
                } catch (\Exception $exception) {
                    trigger_error($exception->getMessage(), E_USER_WARNING);
                }
            }
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
        }

        return null;
    }

    public function id(): int
    {
        return intval($this->extra_args()['Id']);
    }

    public function startDateTime(string $timezone = 'America/New_York'): ?\DateTime
    {
        try {
            $timezone = new DateTimeZone($timezone);

            if (isset($this->extra_args()['StartDateTime'])) {
                try {
                    return new \DateTime( $this->extra_args()['StartDateTime'], $timezone );
                } catch (\Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
            }
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
        }

        return null;
    }
}
