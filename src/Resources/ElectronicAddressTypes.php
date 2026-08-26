<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class ElectronicAddressTypes extends Base implements ResourceInterface
{
    public const RESOURCE = 'ReferenceData/ElectronicAddressTypes';

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected ApiInterface $_api;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();
    }

    /**
     * @return ElectronicAddressType[]
     */
    public function types(): array
    {
        try {
            $data = $this->_api->get(self::RESOURCE);

            return array_map(fn($item) => new ElectronicAddressType($item), $data);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function typeById(int $id): ElectronicAddressType
    {
        $data = $this->_api->get(sprintf('%s/%d', self::RESOURCE, $id));

        return new ElectronicAddressType($data);
    }
}
