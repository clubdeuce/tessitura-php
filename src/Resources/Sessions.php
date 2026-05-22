<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class Sessions extends Base implements ResourceInterface
{
    public const RESOURCE = 'Web/Session';

    protected ApiInterface $_api;

    public function __construct(ApiInterface $api)
    {
        $this->_api = $api;
        parent::__construct();
    }

    public function getByKey(string $key): ?Session
    {
        try {
            $data = $this->_api->get(
                sprintf('%s/%s', self::RESOURCE, $key),
                ['cache_expiration' => 10]
            );

            return is_array($data) ? new Session($data) : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
