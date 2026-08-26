<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Base\Container;
use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Resources\Performances;
use Clubdeuce\Tessitura\Tessitura;
use Clubdeuce\Tessitura\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(Tessitura::class)]
#[UsesClass(Container::class)]
#[UsesClass(Api::class)]
#[UsesClass(Performances::class)]
class TessituraTest extends TestCase
{
    public function testCanCreateFacadeFromConfig(): void
    {
        $tessitura = Tessitura::fromConfig([
            'base_route' => 'https://example.com',
            'username'   => 'username',
            'password'   => 'password',
            'machine'    => 'machine',
            'usergroup'  => 'usergroup',
        ]);

        $this->assertInstanceOf(Tessitura::class, $tessitura);
    }

    public function testExposesUnderlyingContainer(): void
    {
        $tessitura = new Tessitura();

        $this->assertInstanceOf(Container::class, $tessitura->container());
    }

    public function testResolvesApiService(): void
    {
        $tessitura = new Tessitura();

        $this->assertInstanceOf(ApiInterface::class, $tessitura->api());
    }

    public function testResolvesPerformancesResource(): void
    {
        $tessitura = new Tessitura();

        $this->assertInstanceOf(Performances::class, $tessitura->performances());
    }
}