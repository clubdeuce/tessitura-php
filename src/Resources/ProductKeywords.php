<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class ProductKeywords extends Base implements ResourceInterface
{
    public const RESOURCE = 'TXN/ProductKeywords';

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected ApiInterface $_api;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();
    }

    /**
     * @param  int[] $elementIds
     * @return KeywordResult[]
     */
    public function get(array $elementIds = []): array
    {
        if ([] === $elementIds) {
            return [];
        }

        try {
            $data = $this->_api->get(
                sprintf('%s?productionElementIds=%s', self::RESOURCE, implode(',', $elementIds))
            );

            return array_map(fn($item) => new KeywordResult($item), $data);
        } catch (\Exception $e) {
            return [];
        }
    }
}
