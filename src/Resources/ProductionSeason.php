<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Throwable;

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
                $result = DateTime::createFromFormat(' Y-m-d\TG:i:sp', $date, $timezone);
                if ($result === false) {
                    throw new Exception("Invalid date format for FirstPerformanceDate");
                }
                return $result;
            } catch (Throwable $e) {
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
                $result = DateTime::createFromFormat(' Y-m-d\TG:i:sp', $date, $timezone);
                if ($result === false) {
                    throw new Exception("Invalid date format for LastPerformanceDate");
                }
                return $result;
            } catch (Throwable $e) {
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
