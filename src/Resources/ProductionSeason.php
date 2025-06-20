<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

/**
 * @method string[] response()
 */
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
     * @throws InvalidArgumentException
     */
    public function first_performance_date(string $timezone = 'America/New_York'): DateTime|bool
    {
        try {
            $timezone = new DateTimeZone($timezone);
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        if ($date = isset($this->response()['FirstPerformanceDate']) ? $this->response()['FirstPerformanceDate'] : false) {
            try {
                return DateTime::createFromFormat(' Y-m-d\TG:i:sp', $date, $timezone);
            } catch (Exception $e) {
                throw new Exception("Unable to parse FirstPerformanceDate: " . $e->getMessage());
            }
        }

        return false;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function last_performance_date(string $timezone = 'America/New_York'): DateTime|bool
    {
        try {
            $timezone = new DateTimeZone($timezone);
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        if ($date = isset($this->response()['LastPerformanceDate']) ? $this->response()['LastPerformanceDate'] : false) {
            try {
                return DateTime::createFromFormat(' Y-m-d\TG:i:sp', $date, $timezone);
            } catch (Exception $e) {
                throw new Exception("Unable to parse LastPerformanceDate: " . $e->getMessage());
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
