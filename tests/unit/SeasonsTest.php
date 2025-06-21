<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Resources\Season;
use Clubdeuce\Tessitura\Resources\Seasons;
use Clubdeuce\Tessitura\Tests\testCase;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(Seasons::class)]
#[UsesClass(Base::class)]
#[UsesClass(Season::class)]
class SeasonsTest extends testCase
{
    public function testGetById(): void
    {
        try {
            /**
             * @link https://docs.guzzlephp.org/en/stable/testing.html
             */
            $mock = new MockHandler([
                new Response(200, [], file_get_contents(dirname(__DIR__) . '/fixtures/season.json')),
                new InvalidArgumentException('Mock error', 400),
            ]);

            $sut = new Seasons(new Client(['handler' => HandlerStack::create($mock)]));

            try {
                $season = $sut->getById(1);
                $this->assertInstanceOf(Season::class, $season);
                $this->assertNotEquals(0, $season->getId());
                $this->assertNull($sut->getById(1));
            } catch (Exception $e) {
                trigger_error($e->getMessage());
            }
        } catch (\InvalidArgumentException $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testGet(): void
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(dirname(__DIR__) . '/fixtures/seasons.json')),
            new InvalidArgumentException('Mock error', 400),
        ]);

        $sut     = new Seasons(new Client(['handler' => HandlerStack::create($mock)]));
        $seasons = $sut->get();

        $this->assertIsArray($seasons);
        $this->assertNotEmpty($seasons);
        $this->assertContainsOnlyInstancesOf(Season::class, $seasons);

        // check for empty array when there is an API connection error
        $this->expectException(Exception::class);
        $this->assertIsArray($sut->get());
    }
}
