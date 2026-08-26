<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class ProductionSeasons extends Base implements ResourceInterface
{
    public const RESOURCE = 'TXN/ProductionSeasons';

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected ApiInterface $_api;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();
    }

    /**
     * @param  mixed[] $args {
     *     @type int|int[] $seasonIds
     *     @type int|int[] $productionIds
     *     @type int|int[] $ids
     * }
     * @return mixed[]
     */
    public function getAll(array $args = []): array
    {
        $params = array_filter([
            'seasonIds'     => !empty($args['seasonIds']) ? implode(',', (array)$args['seasonIds']) : null,
            'productionIds' => !empty($args['productionIds']) ? implode(',', (array)$args['productionIds']) : null,
            'ids'           => !empty($args['ids']) ? implode(',', (array)$args['ids']) : null,
        ]);

        $query = $params ? '?' . http_build_query($params) : '';

        try {
            $data = $this->_api->get(self::RESOURCE . $query);

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getById(int $id): ?ProductionSeason
    {
        try {
            $data = $this->_api->get(sprintf('%s/%d', self::RESOURCE, $id));

            return new ProductionSeason($data);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param  mixed[] $args {
     *     @type string $ArtistIds            Comma-separated artist IDs
     *     @type string $PerformanceEndDate   ISO 8601 datetime
     *     @type string $PerformanceStartDate ISO 8601 datetime
     *     @type string $SeasonIds            Comma-delimited season IDs
     * }
     * @return mixed[]
     */
    public function search(array $args = []): array
    {
        $body = json_encode($args);

        try {
            $data = $this->_api->post(self::RESOURCE . '/Search', [
                'body'    => $body,
                'headers' => ['Content-Length' => $body ? strlen($body) : 0],
            ]);

            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return mixed[]
     */
    public function getSeasonsForProduction(int $productionId): array
    {
        return $this->getAll(['productionIds' => $productionId]);
    }
}
