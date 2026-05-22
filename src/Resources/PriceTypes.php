<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class PriceTypes extends Base implements ResourceInterface
{
    public const RESOURCE = 'TXN/PriceTypes';

    protected ApiInterface $_api;

    /** @var PriceType[] */
    protected array $_types = [];

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
        if (empty($this->_types)) {
            try {
                $data         = $this->_api->get(self::RESOURCE);
                $this->_types = array_map(fn($item) => new PriceType($item), $data);
            } catch (\Exception $e) {
                return [];
            }
        }

        return $this->_types;
    }

    public function get(int $id): PriceType
    {
        $data = $this->_api->get(sprintf('%s/%d', self::RESOURCE, $id));

        return new PriceType($data);
    }
}
