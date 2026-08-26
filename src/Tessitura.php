<?php

namespace Clubdeuce\Tessitura;

use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Interfaces\CacheInterface;
use Clubdeuce\Tessitura\Resources\PriceTypes;
use Clubdeuce\Tessitura\Resources\ProductKeywords;
use Clubdeuce\Tessitura\Resources\Performances;
use DI\Container;
use DI\ContainerBuilder;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

use function DI\autowire;
use function DI\get;

class Tessitura extends Base\Base
{
    /**
     * The version of the Tessitura API client.
     *
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * The container instance.
     *
     * @var Container
     */
    private static Container $_container;

    public function __construct(array $settings = [])
    {
        $baseRoute = trim((string)($settings['base_route'] ?? $settings['baseRoute'] ?? $settings['rest_api_url'] ?? ''));
        $machine   = trim((string)($settings['machine'] ?? $settings['machine_name'] ?? $settings['location'] ?? ''));
        $usergroup = trim((string)($settings['usergroup'] ?? $settings['userGroup'] ?? $settings['user_group'] ?? $settings['group'] ?? ''));
        $username  = trim((string)($settings['username'] ?? $settings['user_name'] ?? ''));
        $password  = trim((string)($settings['password'] ?? $settings['pass'] ?? ''));
        $version   = trim((string)($settings['version'] ?? '16'));

        $required = [
            'base_route' => $baseRoute,
            'machine'    => $machine,
            'usergroup'  => $usergroup,
            'username'   => $username,
            'password'   => $password,
        ];

        $missing = [];
        foreach ($required as $key => $value) {
            if ($value === '') {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new InvalidArgumentException(sprintf(
                'Missing required Tessitura settings: %s',
                implode(', ', $missing)
            ));
        }

        $client = $settings['http_client'] ?? null;
        if (!$client instanceof Client) {
            $client = new Client();
        }

        $logger = $settings['logger'] ?? null;
        if (!$logger instanceof LoggerInterface) {
            $logger = null;
        }

        $cache = $settings['cache'] ?? null;
        if (!$cache instanceof CacheInterface) {
            $cache = null;
        }

        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            ApiInterface::class => function () use (
                $baseRoute,
                $machine,
                $password,
                $usergroup,
                $username,
                $version,
                $client,
                $logger,
                $cache
            ): ApiInterface {
                return new Api([
                    'base_route' => $baseRoute,
                    'machine'    => $machine,
                    'password'   => $password,
                    'usergroup'  => $usergroup,
                    'username'   => $username,
                    'version'    => $version,
                ], $client, $logger, $cache);
            },
            Api::class          => get(ApiInterface::class),
            Performances::class => autowire()
                ->constructorParameter('productKeywords', get(ProductKeywords::class))
                ->constructorParameter('priceTypes', get(PriceTypes::class)),
            'api'               => get(ApiInterface::class),
            'performances'      => get(Performances::class),
        ]);

        self::$_container = $builder->build();
    }

    public function container(): Container
    {
        return self::$_container;
    }

    public function performances(): Performances
    {
        return $this->container()->get('performances');
    }

    public function webContents(): Resources\WebContents
    {
        return $this->container()->get(Resources\WebContents::class);
    }

    public function productKeywords(): ProductKeywords
    {
        return $this->container()->get(ProductKeywords::class);
    }

    public function constituentTypes(): Resources\ConstituentTypes
    {
        return $this->container()->get(Resources\ConstituentTypes::class);
    }

    public function originalSources(): Resources\OriginalSources
    {
        return $this->container()->get(Resources\OriginalSources::class);
    }

    public function electronicAddressTypes(): Resources\ElectronicAddressTypes
    {
        return $this->container()->get(Resources\ElectronicAddressTypes::class);
    }
}
