<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class AddressTypes extends Base implements ResourceInterface
{
    public const RESOURCE = 'ReferenceData/AddressTypes';

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected ApiInterface $_api;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();
    }

    /**
     * @return AddressType[]
     */
    public function types(): array
    {
        try {
            $data = $this->_api->get(self::RESOURCE);

            return array_map(fn($item) => new AddressType($item), $data);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getById(int $id): AddressType
    {
        $data = $this->_api->get(sprintf('%s/%d', self::RESOURCE, $id));

        return new AddressType($data);
    }
}
