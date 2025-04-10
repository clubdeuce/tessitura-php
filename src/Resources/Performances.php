<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Helpers\Api;
use DateTime;
use Clubdeuce\Tessitura\Resources\PerformanceZoneAvailability as PZA;

class Performances extends Base
{

    const RESOURCE = 'TXN/Performances';
    protected Api $_api;

    public function __construct(Api|null $api = null)
    {
        if (empty ($api)) {
            $api = new Api();
        }

        $this->_api = $api;
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
        $body = json_encode($args);

        $args = array(
            'body' => $body,
            'headers' => [
                'Content-Length' => $body ? strlen($body) : 0,
            ]
        );

        $results = $this->_api->post($endpoint, $args);

        if (!is_array($results)) {
            trigger_error('Expected an array. Received ' . var_export($results, true), E_USER_WARNING);
            return [];
        }

        return array_map(function ($item) {
            return new Performance($item);
        }, $results);
    }

    /**
     * @return Performance[]
     */
    public function get_performances_for_production_season(int $ps_id): array
    {
        return $this->search([
            'ProductionSeasonIds' => (string)$ps_id,
        ]);
    }

    public function get_tickets_start_at( int $performance_id ): int {

        $best_price      = 0;
        $available_zones = [];
        $zones           = $this->getPerformanceZoneAvailabilities( $performance_id );
        $prices          = $this->getPricesForPerformance( $performance_id );

        // Filter out zones that do not have any available seats
        foreach($zones as $zone) {
            if ( $zone->availableCount() > 0 ) {
                $available_zones[] = $zone->zone()->id;
            }
        }

        foreach( $prices as $price ) {
            // is this price for a zone that has available seats?
            if ( in_array( $price->zone_id(), $available_zones ) ) {
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
     * @param int $performance_id The ID of the performance for which to retrieve zone availabilities.
     *
     * @return PerformanceZoneAvailability[] An array of zone availability objects, mapped from the API response.
     *
     * @link https://docs.tessitura.com/REST_v151/TessituraService/HELP/API/GET_TXN_PERFORMANCES_ZONES_PERF.HTM
     */
    public function getPerformanceZoneAvailabilities(int $performance_id ): array {

        try{
            $data = $this->_api->get( sprintf( '%1$s/Zones?performanceIds=%2$s', self::RESOURCE, $performance_id ) );

            return array_map( [ $this, 'makeNewZoneAvailability'], $data );
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
     * @param $performance_id
     * @param array $args
     *
     * @return Price_Summary[]
     *
     * @link https://www.tessituranetwork.com/REST_v151/TessituraService/HELP/API/GET_TXN_PERFORMANCES_PRICES_PER.HTM
     */
    function getPricesForPerformance($performance_id, $args = array()) : array {

        $prices = array();
        $args   = array(
            'body' => array(
                'performanceIds'       => $performance_id,
                'includeOnlyBasePrice' => 'true',
            ),
        );

        try {
            $results = $this->_api->get( self::RESOURCE . '/Prices', $args );
            foreach ( $results as $item ) {
                $prices[] = new PriceSummary($item);
            }
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        unset( $results );

        return $prices;

    }
}
