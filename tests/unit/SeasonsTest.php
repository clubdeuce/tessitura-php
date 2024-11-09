<?php

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Resources\Season;
use Clubdeuce\Tessitura\Resources\Seasons;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use tessitura\tests\includes\testCase;

#[CoversClass(Seasons::class)]
#[UsesClass(Base::class)]
#[UsesClass(Season::class)]
class SeasonsTest extends TestCase
{

    public function testGetById()
    {
        try {
            /**
             * @link https://docs.guzzlephp.org/en/stable/testing.html
             */
            $mock = new MockHandler([
                new Response(200, [], file_get_contents(dirname(__DIR__) . '/fixtures/season.json')),
                new GuzzleHttp\Exception\InvalidArgumentException('Mock error', 400)
            ]);

            $sut = new Seasons(new Client(['handler' => HandlerStack::create($mock)]));
            $season = $sut->getById(1);

            $this->assertInstanceOf(Season::class, $season);
            $this->assertNotEquals(0, $season->id());
            $this->assertNull($sut->getById(1));
        } catch (\InvalidArgumentException $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testGet(): void
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(dirname(__DIR__) . '/fixtures/seasons.json')),
            new GuzzleHttp\Exception\InvalidArgumentException('Mock error', 400)
        ]);

        $sut = new Seasons(new Client(['handler' => HandlerStack::create($mock)]));
        $seasons = $sut->get();

        $this->assertIsArray($seasons);
        $this->assertNotEmpty($seasons);
        $this->assertContainsOnlyInstancesOf(Season::class, $seasons);

        $this->assertIsArray($sut->get());
    }
}
