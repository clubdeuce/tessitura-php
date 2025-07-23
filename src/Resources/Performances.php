<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;
use DateTime;
use Exception;
use Throwable;

/**
 * Class to interact with performance data from a backend API.
 */
class Performances extends Base implements ResourceInterface
{
    public const RESOURCE = 'TXN/Performances';
    protected ApiInterface $_api;

    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();
    }

    /**
     * Get upcoming performances
     *
     * @param int $days Number of days to look ahead for performances. Defaults to thirty days.
     * @return Performance[]
     * @throws Exception
     */
    public function getUpcomingPerformances(int $days = 30): array
    {
        try {
            $start = new DateTime();
            $end   = new DateTime("now + {$days} days");

            return $this->getPerformancesBetween($start, $end);
        } catch (Throwable $e) {
            throw new Exception("Unable to get upcoming performances: " . $e->getMessage());
        }
    }

    /**
     * Get performances between two dates.
     *
     * @param DateTime $start
     * @param DateTime $end
     * @return Performance[]
     */
    public function getPerformancesBetween(DateTime $start, DateTime $end): array
    {
        $sorted       = [];
        $performances = $this->search([
            'PerformanceStartDate' => $start->format(DATE_ATOM),
            'PerformanceEndDate'   => $end->format(DATE_ATOM),
        ]);

        foreach ($performances as $performance) {
            try {
                $date = $performance->date();
                if (!is_null($date)) {
                    $sorted[$date->getTimestamp()] = $performance;
                }
            } catch (Throwable $e) {
                // Skip performances with invalid dates rather than stopping the entire operation
                continue;
            }
        }

        ksort($sorted);

        return $sorted;
    }

    /**
     * @param int $psId
     * @return Performance[]
     */
    public function getPerformancesForProductionSeason(int $psId): array
    {
        return $this->search([
            'ProductionSeasonIds' => (string)$psId,
        ]);
    }

    /**
     * @param  mixed[] $args
     * @return Performance[]
     */
    public function search(array $args = []): array
    {
        $endpoint = sprintf('%1$s/Search', self::RESOURCE);
        $body     = json_encode($args);

        $args = [
            'body'    => $body,
            'headers' => [
                'Content-Length' => $body ? strlen($body) : 0,
            ],
        ];

        $results = $this->_api->post($endpoint, $args);

        if (!is_array($results)) {
            return [];
        }

        return array_map(fn ($item) => new Performance($item), $results);
    }

    /**
     * @param int $performanceId
     * @return PerformanceZoneAvailability[]
     */
    public function getPerformanceZoneAvailabilities(int $performanceId): array
    {
        try {
            $data = $this->_api->get(sprintf('%1$s/Zones?performanceIds=%2$s', self::RESOURCE, $performanceId));

            return array_map([$this, 'makeNewZoneAvailability'], $data);
        } catch (Exception $e) {
            //trigger_error("Exception in getPerformanceZoneAvailabilities: " . $e->getMessage());

            return [];
        }
    }

    /**
     * Create a new PerformanceZoneAvailability instance from the provided data.
     *
     * @param mixed[] $data
     * @return PerformanceZoneAvailability
     */
    protected function makeNewZoneAvailability(array $data): PerformanceZoneAvailability
    {
        $data = $this->parseArgs($data, [
            'AvailableCount'   => 0,
            'Id'               => 0,
            'Inactive'         => false,
            'PerformanceId'    => 0,
            'SectionSummaries' => null,
            'Zone'             => null,
        ]);

        return new PerformanceZoneAvailability([
            'availableCount' => $data['AvailableCount'],
            'zone'           => $data['Zone'],
        ]);
    }
}
