<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class WebContents extends Base implements ResourceInterface
{
    public const RESOURCE = 'TXN/WebContents';

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
     * @return WebContent[]
     */
    public function get(array $elementIds = []): array
    {
        if ([] === $elementIds) {
            return [];
        }

        try {
            $items = [];
            $data  = $this->_api->get(
                sprintf('%s?productionElementIds=%s', self::RESOURCE, implode(',', $elementIds))
            );

            foreach ($data as $result) {
                foreach ($result['WebContents'] ?? [] as $content) {
                    $items[] = new WebContent($content);
                }
            }

            return $items;
        } catch (\Exception $e) {
            return [];
        }
    }
}
