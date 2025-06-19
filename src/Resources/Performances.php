<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;
use DateTime;
use Clubdeuce\Tessitura\Resources\PerformanceZoneAvailability as PZA;

class Performances extends Base implements ResourceInterface
{

    const RESOURCE = 'TXN/Performances';
    protected ApiInterface $_api;

    /**
     * Constructor method for initializing the resource with dependencies.
     *
     * @param ApiInterface $api The API client to use for requests.
     * @return void
     */
    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();;
    }

    /**
     * @param int $days
     * @return Performance[]
     */
    public function get_upcoming_performances(int $days = 30): array
    {
        try {
            $start = new DateTime();
            $end = new DateTime("now + {$days} days");

            return $this->get_performances_between($start, $end);
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return [];
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return Performance[]
     */
    public function get_performances_between(DateTime $start, DateTime $end): array
    {
        $sorted = [];
        $performances = $this->search(array(
            'PerformanceStartDate' => $start->format(DATE_ATOM),
            'PerformanceEndDate'   => $end->format(DATE_ATOM),
        ));

        // generate a hash table
        foreach ($performances as $performance) {
            try {
                $date = $performance->date();
                if (!is_null($date))
                    $sorted[$date->getTimestamp()] = $performance;
            } catch (\Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        ksort($sorted);

        return $sorted;
    }

    /**
     * @param string[] $args
     *
     * @return Performance[]
     * @link   https://docs.tessitura.com/REST_v151/TessituraService/HELP/API/POST_TXN_PERFORMANCES_SEARCH.HTM
     */
    public function search(array $args = []): array
    {
        $endpoint = sprintf('%1$s/Search', self::RESOURCE);
        $body     = json_encode($args);

        $args = array(
            'body' => $body,
            'headers' => [
                'Content-Length' => $body ? strlen($body) : 0,
            ]
        );

        $results = $this->_api->post($endpoint, $args);

        return array_map(function ($item) {
            return new Performance($item);
        }, $results);
    }

    /**
     * @return Performance[]
     */
    public function get_performances_for_production_season(int $psId): array
    {
        return $this->search([
            'ProductionSeasonIds' => (string)$psId,
        ]);
    }

    public function getTicketsStartAt(int $performanceId): int {

        $best_price      = 0;
        $available_zones = [];
        $zones           = $this->getPerformanceZoneAvailabilities($performanceId);
        $prices          = $this->getPricesForPerformance($performanceId);

        // Filter out zones that do not have any available seats
        foreach($zones as $zone) {
            if ( $zone->availableCount() > 0 ) {
                $available_zones[] = $zone->zone()->id;
            }
        }

        foreach( $prices as $price ) {
            // is this price for a zone that has available seats?
            if ( in_array( $price->zoneId(), $available_zones ) ) {
                // It is. Is this price 0? Then skip. Is this price more than the current best price? Then skip.
                if ( $price->price() && ( $price->price() < $best_price || 0 === $best_price ) ) {
                    // The current price is non-zero. Update the best price to this value
                    $best_price = $price->price();
                }
            }
        }

        return intval( $best_price );

    }

    /**
     * @param int $performanceId The ID of the performance for which to retrieve zone availabilities.
     *
     * @return PerformanceZoneAvailability[] An array of zone availability objects, mapped from the API response.
     *
     * @link https://docs.tessitura.com/REST_v151/TessituraService/HELP/API/GET_TXN_PERFORMANCES_ZONES_PERF.HTM
     */
    public function getPerformanceZoneAvailabilities(int $performanceId ): array {

        try{
            $data = $this->_api->get( sprintf( '%1$s/Zones?performanceIds=%2$s', self::RESOURCE, $performanceId ) );

            if ( is_array( $data ) ) {
                return array_map( [$this, 'makeNewZoneAvailability'], $data );
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }

    }

    /**
     * @param  string[] $data
     * @return PZA
     */
    public function makeNewZoneAvailability(array $data) : PZA {

        $data = $this->parse_args( $data, [
            'AvailableCount'   => 0,
            'Id'               => 0,
            'Inactive'         => false,
            'PerformanceId'    => 0,
            'SectionSummaries' => null,
            'Zone'             => null,
        ] );

        return new PZA( [
            'availableCount' => $data['AvailableCount'],
            'zone'           => $data['Zone'],
        ] );

    }

    /**
     * @param  int $performanceId
     *
     * @return PriceSummary[]
     *
     * @link https://www.tessituranetwork.com/REST_v151/TessituraService/HELP/API/GET_TXN_PERFORMANCES_PRICES_PER.HTM
     */
    public function getPricesForPerformance(int $performanceId) : array {

        $prices = array();
        $args   = array(
            'body' => array(
                'performanceIds'       => $performanceId,
                'includeOnlyBasePrice' => 'true',
            ),
        );

        try {
            $results = $this->_api->get( self::RESOURCE . '/Prices', $args );

            if (is_array($results)) {
                foreach ( $results as $item ) {
                    $prices[] = new PriceSummary($item);
                }
            }
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        unset( $results );

        return $prices;

    }
}
