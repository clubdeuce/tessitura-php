<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class OriginalSources extends Base implements ResourceInterface
{
    public const RESOURCE = 'ReferenceData/OriginalSources';

    protected ApiInterface $_api;

    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();
    }

    /**
     * @return OriginalSource[]
     */
    public function sources(): array
    {
        try {
            $data = $this->_api->get(self::RESOURCE);

            return array_map(fn($item) => new OriginalSource($item), $data);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function sourceById(int $id): OriginalSource
    {
        $data = $this->_api->get(sprintf('%s/%d', self::RESOURCE, $id));

        return new OriginalSource($data);
    }
}
