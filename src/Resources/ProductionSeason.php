<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use DateTime;
use DateTimeZone;
use Clubdeuce\Tessitura\Resources\Performance;

/**
 * @method array response()
 */
class ProductionSeason extends Base
{

    protected array $_response = [];

    /**
     * @var Performance[]
     */
    protected array $_performances = [];

    /**
     * @throws \Exception
     */
    public function first_performance_date(string $timezone = 'America/New_York'): DateTime|bool
    {
        try {
            $timezone = new DateTimeZone($timezone);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        if ($date = isset($this->response()['FirstPerformanceDate']) ? $this->response()['FirstPerformanceDate'] : false) {
            try {
                return DateTime::createFromFormat(' Y-m-d\TG:i:sp', $date, $timezone);
            } catch (\Exception $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function last_performance_date(string $timezone = 'America/New_York'): DateTime|bool
    {
        try {
            $timezone = new DateTimeZone($timezone);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage(), E_USER_WARNING);
        }

        if ($date = isset($this->response()['LastPerformanceDate']) ? $this->response()['LastPerformanceDate'] : false) {
            try {
                return DateTime::createFromFormat(' Y-m-d\TG:i:sp', $date, $timezone);
            } catch (\Exception $e) {
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
