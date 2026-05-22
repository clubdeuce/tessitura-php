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

    /** Tessitura "Mode of Sale" ID for web sales. */
    public const MODE_OF_SALE_WEB = 5;

    /** Tessitura PriceTypeId for the standard "Single Ticket" price. */
    public const PRICE_TYPE_SINGLE = 1;

    /**
     * Cache TTL (seconds) for price/zone/SeatFees lookups.
     *
     * 5 minutes is the compromise between compliance freshness and listing-page
     * performance when iterating many performances.
     */
    public const PRICE_LOOKUP_CACHE_TTL = 5 * 60;

    protected ApiInterface $_api;
    protected ?ProductKeywords $_productKeywords;
    protected ?PriceTypes $_priceTypes;

    public function __construct(
        ApiInterface $api,
        ?ProductKeywords $productKeywords = null,
        ?PriceTypes $priceTypes = null
    ) {
        $this->_api            = $api;
        $this->_productKeywords = $productKeywords;
        $this->_priceTypes     = $priceTypes;
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
                continue;
            }
        }

        ksort($sorted);

        return $sorted;
    }

    /**
     * Get performances on a specific day.
     *
     * @return Performance[]
     */
    public function getPerformancesOn(DateTime $day): array
    {
        return $this->search([
            'PerformanceStartDate' => $day->format(DATE_ATOM),
            'PerformanceEndDate'   => (clone $day)->setTime(23, 59, 59)->format(DATE_ATOM),
        ]);
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

        return array_map(fn($item) => $this->makePerformance($item), $results);
    }

    /**
     * @param int $performanceId
     * @return PerformanceZoneAvailability[]
     */
    public function getPerformanceZoneAvailabilities(int $performanceId): array
    {
        try {
            $data = $this->_api->get(
                sprintf('%1$s/Zones?performanceIds=%2$s', self::RESOURCE, $performanceId),
                ['cache_expiration' => self::PRICE_LOOKUP_CACHE_TTL]
            );

            return array_map([$this, 'makeNewZoneAvailability'], $data);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get base prices for a performance.
     *
     * @param  int     $performanceId
     * @param  mixed[] $args
     * @return PriceSummary[]
     */
    public function getPricesForPerformance(int $performanceId, array $args = []): array
    {
        $requestOpts = [];
        if (array_key_exists('cache_expiration', $args)) {
            $requestOpts['cache_expiration'] = $args['cache_expiration'];
            unset($args['cache_expiration']);
        }

        $params = http_build_query(array_merge($args, [
            'performanceIds'       => $performanceId,
            'includeOnlyBasePrice' => 'true',
        ]));

        try {
            $data = $this->_api->get(
                sprintf('%s/Prices?%s', self::RESOURCE, $params),
                $requestOpts
            );

            return array_map(fn($item) => new PriceSummary($item), $data);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get prices for a specific zone, with the IsBase row sorted first.
     *
     * @param  int     $performanceId
     * @param  int     $zoneId
     * @param  mixed[] $args
     * @return mixed[]
     */
    public function getPerformancePricesForZone(int $performanceId, int $zoneId, array $args = []): array
    {
        $prices = [];
        $priceTypeDescriptions = [];

        $requestOpts = [];
        if (array_key_exists('cache_expiration', $args)) {
            $requestOpts['cache_expiration'] = $args['cache_expiration'];
            unset($args['cache_expiration']);
        }

        $params = http_build_query(array_merge([
            'performanceIds' => $performanceId,
            'modeOfSaleId'   => self::MODE_OF_SALE_WEB,
            'priceTypeIds'   => self::PRICE_TYPE_SINGLE,
        ], $args));

        try {
            if ($this->_priceTypes !== null) {
                foreach ($this->_priceTypes->getAll() as $priceType) {
                    $priceTypeDescriptions[$priceType->getId()] = $priceType->getDescription();
                }
            }

            $response = $this->_api->get(
                sprintf('%s/Prices?%s', self::RESOURCE, $params),
                $requestOpts
            );

            $baseRow    = null;
            $otherRows  = [];

            foreach ($response as $item) {
                if ($zoneId !== (int)($item['ZoneId'] ?? -1)) {
                    continue;
                }

                $priceTypeId = (int)($item['PriceTypeId'] ?? 0);
                $description = $priceTypeDescriptions[$priceTypeId] ?? '';

                $row = [
                    'price_type_id' => $priceTypeId,
                    'description'   => $description,
                    'price'         => (float)($item['Price'] ?? 0),
                    'is_base'       => !empty($item['IsBase']),
                    'enabled'       => !empty($item['Enabled']),
                ];

                if (null === $baseRow && $row['is_base'] && $row['enabled']) {
                    $baseRow = $row;
                } else {
                    $otherRows[] = $row;
                }
            }

            if (null === $baseRow && !empty($otherRows)) {
                $baseRow = array_shift($otherRows);
            }

            if (null !== $baseRow) {
                $prices[] = $baseRow;
            }
            foreach ($otherRows as $row) {
                $prices[] = $row;
            }
        } catch (Exception $e) {
            // return empty
        }

        return $prices;
    }

    /**
     * Sum of per-ticket fees for a zone, or null if the lookup fails.
     */
    public function getSeatFeesForZone(int $performanceId, int $zoneId): ?float
    {
        $params   = http_build_query([
            'modeOfSaleId' => self::MODE_OF_SALE_WEB,
            'priceTypeIds' => self::PRICE_TYPE_SINGLE,
        ]);
        $endpoint = sprintf('%s/%d/SeatFees?%s', self::RESOURCE, $performanceId, $params);

        try {
            $response = $this->_api->get(
                $endpoint,
                ['cache_expiration' => self::PRICE_LOOKUP_CACHE_TTL]
            );

            $matched   = false;
            $feeTotal  = 0.0;

            foreach ($response as $row) {
                if (!isset($row['ZoneId']) || $zoneId !== (int)$row['ZoneId']) {
                    continue;
                }
                $matched   = true;
                $feeTotal += isset($row['FeeAmount']) ? (float)$row['FeeAmount'] : 0.0;
            }

            return $matched ? $feeTotal : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Compute the all-in "starting at" price for a performance.
     *
     * Walks all available zones, picks the lowest IsBase price, adds seat fees.
     * Returns 0.0 when no zones are on sale. Falls back to 0.0 (not an error)
     * when SeatFees returns no data; callers that need a WP fee fallback (e.g.
     * DSO's Fees::ticket_fees()) should subclass and override this method.
     *
     * @param  mixed[] $args
     */
    public function getTicketsStartAt(int $performanceId, array $args = []): float
    {
        $bestPrice = 0.0;
        $bestZone  = 0;
        $zones     = $this->getPerformanceZoneAvailabilities($performanceId);

        if (!array_key_exists('cache_expiration', $args)) {
            $args['cache_expiration'] = self::PRICE_LOOKUP_CACHE_TTL;
        }

        foreach ($zones as $zone) {
            if (!$zone->availableCount()) {
                continue;
            }

            $zoneInfo = $zone->zone();
            $zoneId   = $zoneInfo->id;
            $prices   = $this->getPerformancePricesForZone($performanceId, $zoneId, $args);

            if (isset($prices[0]['price'])) {
                $p = (float)$prices[0]['price'];
                if (0.0 === $bestPrice || $p < $bestPrice) {
                    $bestPrice = $p;
                    $bestZone  = $zoneId;
                }
            }
        }

        if (0.0 === $bestPrice) {
            return 0.0;
        }

        $fees = $this->getSeatFeesForZone($performanceId, $bestZone);

        return $bestPrice + ($fees ?? 0.0);
    }

    /**
     * Returns true if the performance has any of the keywords in $keywords.
     * When no ProductKeywords service was injected, always returns false.
     *
     * @param  string[]|int[] $keywords Description or ID values to match against.
     */
    public function filterPerformanceByKeywords(Performance $performance, array $keywords): bool
    {
        if ($this->_productKeywords === null) {
            return false;
        }

        $results = $this->_productKeywords->get([$performance->id()]);

        foreach ($results as $result) {
            foreach ($result->keywords() as $keyword) {
                if (
                    in_array($keyword['Description'] ?? null, $keywords, true)
                    || in_array($keyword['Id'] ?? null, $keywords, true)
                ) {
                    return true;
                }
            }
        }

        return false;
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

    /**
     * Factory method for creating Performance objects.
     * Override in a subclass to produce enriched performance instances.
     *
     * @param  mixed[] $data
     */
    protected function makePerformance(array $data): Performance
    {
        return new Performance($data);
    }
}
