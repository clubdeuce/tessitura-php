<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\ResourceInterface;

class Sessions extends Base implements ResourceInterface
{
    public const RESOURCE = 'Web/Session';
    private const CACHE_EXPIRATION = 10 * 60;

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected ApiInterface $_api;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

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
                ['cache_expiration' => self::CACHE_EXPIRATION]
            );

            return new Session($data);
        } catch (\Exception $e) {
            return null;
        }
    }
}
