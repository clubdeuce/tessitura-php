<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

class ProductionSeason extends Base
{
    /**
     * @var string[]
     */
    protected array $_response = [];

    /**
     * @var Performance[]
     */
    protected array $_performances = [];

    /**
     * Get the response data for this production season.
     *
     * @return mixed[] The response data
     */
    public function response(): array
    {
        return $this->_response;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function firstPerformanceDate(string $timezone = 'America/New_York'): DateTime|bool
    {
        try {
            $timezone = new DateTimeZone($timezone);
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        $date = isset($this->response()['FirstPerformanceDate']) ? $this->response()['FirstPerformanceDate'] : false;
        if ($date) {
            try {
                return DateTime::createFromFormat(' Y-m-d\TG:i:sp', $date, $timezone);
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }

        return false;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function lastPerformanceDate(string $timezone = 'America/New_York'): DateTime|bool
    {
        try {
            $timezone = new DateTimeZone($timezone);
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        $date = isset($this->response()['LastPerformanceDate']) ? $this->response()['LastPerformanceDate'] : false;
        if ($date) {
            try {
                return DateTime::createFromFormat(' Y-m-d\TG:i:sp', $date, $timezone);
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }

        return false;
    }

    /**
     * @return Performance[]
     */
    public function performances(): array
    {
        return $this->_performances;
    }
}
