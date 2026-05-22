<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class PriceTypes extends Base implements ResourceInterface
{
    public const RESOURCE = 'TXN/PriceTypes';

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected ApiInterface $_api;

    /** @var PriceType[] */
    protected array $_types = [];

    /** @var array<int, PriceType> */
    protected array $_typesById = [];

    protected bool $_loadedAllTypes = false;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();
    }

    /**
     * @return PriceType[]
     */
    public function types(): array
    {
        return $this->getAll();
    }

    /**
     * @return PriceType[]
     */
    public function getAll(): array
    {
        if (!$this->_loadedAllTypes) {
            try {
                $data             = $this->_api->get(self::RESOURCE);
                $this->_types     = [];
                $this->_typesById = [];

                foreach ($data as $item) {
                    $this->cacheType(new PriceType($item));
                }

                $this->_loadedAllTypes = true;
            } catch (\Exception $e) {
                return [];
            }
        }

        return $this->_types;
    }

    public function get(int $id): PriceType
    {
        if (isset($this->_typesById[$id])) {
            return $this->_typesById[$id];
        }

        $data = $this->_api->get(sprintf('%s/%d', self::RESOURCE, $id));

        return $this->cacheType(new PriceType($data));
    }

    private function cacheType(PriceType $type): PriceType
    {
        $id = $type->getId();

        if (!isset($this->_typesById[$id])) {
            $this->_types[] = $type;
        }

        $this->_typesById[$id] = $type;

        return $type;
    }
}
