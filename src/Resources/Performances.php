<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Helpers\Api;
use DateTime;

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
     * @param array $args
     *
     * @return Performance[]
     */
    public function get_performances_between(DateTime $start, DateTime $end): array
    {
        $sorted = [];
        $performances = $this->search(array(
            'PerformanceStartDate' => $start->format(DATE_ATOM),
            'PerformanceEndDate' => $end->format(DATE_ATOM),
        ));

        // generate a hash table
        foreach ($performances as $performance) {
            $sorted[$performance->date()->getTimestamp()] = $performance;
        }

        ksort($sorted);

        return $sorted;
    }

    /**
     * @param array $args
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
                'Content-Length' => strlen($body)
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
     * @param int $psid
     *
     * @return Performance[]
     */
    public function get_performances_for_production_season(int $ps_id): array
    {
        return $this->search([
            'ProductionSeasonIds' => $ps_id,
        ]);
    }

}
