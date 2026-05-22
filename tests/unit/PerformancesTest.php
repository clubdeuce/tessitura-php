<?php

namespace Clubdeuce\Tessitura\Tests;

use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Resources\Performance;
use Clubdeuce\Tessitura\Resources\Performances;
use Clubdeuce\Tessitura\Resources\PerformanceZoneAvailability;
use Clubdeuce\Tessitura\Resources\PriceSummary;
use Clubdeuce\Tessitura\Resources\PriceType;
use Clubdeuce\Tessitura\Resources\PriceTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionMethod;

#[CoversClass(Performances::class)]
#[UsesClass(Performance::class)]
#[UsesClass(Api::class)]
class PerformancesTest extends testCase
{
    /**
     * @throws Exception
     */
    public function testSearchReturnsEmptyArray()
    {
        $api = $this->createMock(Api::class);
        $api->method('post')->willReturn([]);

        $sut    = new Performances($api);
        $result = $sut->search();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testSearch()
    {
        $api = $this->createMock(Api::class);
        $api->method('post')
            ->willReturn(
                json_decode(
                    file_get_contents(dirname(__DIR__) . '/fixtures/performances.json'),
                    'associative'
                )
            );

        $sut    = new Performances($api);
        $result = $sut->search();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetPerformancesBetween()
    {
        $api = $this->createMock(Api::class);
        $api->method('post')
            ->willReturn(
                json_decode(
                    file_get_contents(dirname(__DIR__) . '/fixtures/performances.json'),
                    'associative'
                )
            );

        $sut = new Performances($api);

        // the dates here are irrelevant, as we are using a mock api response
        $result = $sut->getPerformancesBetween(new \DateTime(), new \DateTime());

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $previous = $result[array_key_first($result)];

        foreach ($result as $index => $current) {
            $this->assertEquals(
                $index,
                $current->date()->getTimestamp(),
                'The array index is not the performance timestamp.'
            );
            $this->assertGreaterThanOrEqual(
                $previous->date()->getTimestamp(),
                $index,
                'The performance array is not sorted correctly.'
            );
            $previous = $current;
        }
    }

    /**
     * @throws Exception
     */
    public function testGetPerformancesForProductionSeason(): void
    {
        $api = $this->createMock(Api::class);
        $api->method('post')
            ->willReturn(
                json_decode(
                    file_get_contents(
                        dirname(__DIR__) . '/fixtures/performances.json'
                    ),
                    'associative'
                )
            );

        $sut      = new Performances($api);
        $upcoming = $sut->getPerformancesForProductionSeason(35);

        $this->assertIsArray($upcoming);
        $this->assertNotEmpty($upcoming);
    }

    /**
     * @throws Exception
     */
    public function testGetPerformancesForProductionSeasonError(): void
    {
        $api = $this->createMock(Api::class);
        $api->method('post')
            ->willReturn([]);

        $sut      = new Performances($api);
        $upcoming = @$sut->getPerformancesForProductionSeason(35);

        $this->assertIsArray($upcoming);
        $this->assertEmpty($upcoming);
    }

    /**
     * @throws Exception
     */
    public function testMakeNewZoneAvailability(): void
    {
        $rawData = [];

        if (file_exists(dirname(__DIR__) . '/fixtures/performance-zones.json')) {
            $rawData = file_get_contents(dirname(__DIR__) . '/fixtures/performance-zones.json');
        }

        $data = json_decode($rawData, true);

        $this->assertNotEmpty($data);

        $api  = $this->createMock(Api::class);
        $sut  = new Performances($api);

        $reflection = new ReflectionMethod($sut, 'makeNewZoneAvailability');
        $zone       = $reflection->invokeArgs($sut, [$data[0]]);

        $this->assertInstanceOf(PerformanceZoneAvailability::class, $zone);
    }

    #[Depends('testMakeNewZoneAvailability')]
    public function testGetZoneAvailabilities()
    {
        try {
            $api = $this->createMock(Api::class);
            $api->method('get')
                ->willReturn(
                    json_decode(
                        file_get_contents(
                            dirname(__DIR__) . '/fixtures/performance-zones.json'
                        ),
                        true
                    )
                );

            $sut    = new Performances($api);
            $result = $sut->getPerformanceZoneAvailabilities(12345);

            $this->assertIsArray($result);
            $this->assertContainsOnlyObject($result);
        } catch (Exception $e) {
            $this->fail('Exception was thrown: ' . $e->getMessage());
        }
    }

    public function testGetZoneAvailabilitiesError()
    {
        try {
            $api = $this->createMock(Api::class);
            $api->method('get')
                ->willThrowException(new \Exception('Mock error', 400));

            $sut    = new Performances($api);
            $result = $sut->getPerformanceZoneAvailabilities(12345);

            $this->assertIsArray($result);
            $this->assertEmpty($result);
        } catch (Exception $e) {
            $this->fail('Exception was thrown: ' . $e->getMessage());
        }
    }

    public function testGetPricesForPerformanceUsesQueryParameters(): void
    {
        $api = $this->createMock(Api::class);
        $api->expects($this->once())
            ->method('get')
            ->with(
                'TXN/Performances/Prices?performanceIds=123&includeOnlyBasePrice=true',
                []
            )
            ->willReturn([
                [
                    'Enabled'       => true,
                    'PerformanceId' => 123,
                    'Price'         => 25.50,
                    'ZoneId'        => 809,
                ],
            ]);

        $sut    = new Performances($api);
        $result = $sut->getPricesForPerformance(123);

        $this->assertContainsOnlyInstancesOf(PriceSummary::class, $result);
        $this->assertSame(123, $result[0]->performanceId());
    }

    public function testGetPerformancePricesForZoneUsesLoadedPriceTypes(): void
    {
        $api = $this->createMock(Api::class);
        $api->expects($this->once())
            ->method('get')
            ->with(
                'TXN/Performances/Prices?performanceIds=15024&modeOfSaleId=5&priceTypeIds=1',
                []
            )
            ->willReturn([
                [
                    'ZoneId'      => 809,
                    'PriceTypeId' => 1,
                    'Price'       => 25.00,
                    'IsBase'      => true,
                    'Enabled'     => true,
                ],
                [
                    'ZoneId'      => 809,
                    'PriceTypeId' => 2,
                    'Price'       => 20.00,
                    'IsBase'      => false,
                    'Enabled'     => true,
                ],
            ]);

        $priceTypes = $this->createMock(PriceTypes::class);
        $priceTypes->expects($this->once())
            ->method('getAll')
            ->willReturn([
                new PriceType(['Id' => 1, 'Description' => 'Adult']),
                new PriceType(['Id' => 2, 'Description' => 'Student']),
            ]);
        $priceTypes->expects($this->never())->method('get');

        $sut    = new Performances($api, null, $priceTypes);
        $result = $sut->getPerformancePricesForZone(15024, 809);

        $this->assertSame('Adult', $result[0]['description']);
        $this->assertSame('Student', $result[1]['description']);
    }

    public function testGetSeatFeesForZoneSumsMatchingFees(): void
    {
        $api = $this->createMock(Api::class);
        $api->expects($this->once())
            ->method('get')
            ->with(
                'TXN/Performances/15024/SeatFees?modeOfSaleId=5&priceTypeIds=1',
                ['cache_expiration' => Performances::PRICE_LOOKUP_CACHE_TTL]
            )
            ->willReturn([
                ['ZoneId' => 809, 'FeeAmount' => 2.50],
                ['ZoneId' => 809, 'FeeAmount' => 1.25],
                ['ZoneId' => 810, 'FeeAmount' => 9.99],
            ]);

        $sut = new Performances($api);

        $this->assertSame(3.75, $sut->getSeatFeesForZone(15024, 809));
    }

    public function testGetTicketsStartAtUsesLowestAvailableZoneAndAddsFees(): void
    {
        $api = $this->createMock(Api::class);
        $sut = $this->getMockBuilder(Performances::class)
            ->setConstructorArgs([$api])
            ->onlyMethods([
                'getPerformanceZoneAvailabilities',
                'getPerformancePricesForZone',
                'getSeatFeesForZone',
            ])
            ->getMock();

        $sut->expects($this->once())
            ->method('getPerformanceZoneAvailabilities')
            ->with(15024)
            ->willReturn([
                $this->makeZoneAvailability(810, 0),
                $this->makeZoneAvailability(809, 10),
                $this->makeZoneAvailability(811, 4),
            ]);

        $sut->expects($this->exactly(2))
            ->method('getPerformancePricesForZone')
            ->willReturnCallback(
                function (int $performanceId, int $zoneId, array $args): array {
                    $this->assertSame(15024, $performanceId);
                    $this->assertSame(Performances::PRICE_LOOKUP_CACHE_TTL, $args['cache_expiration']);

                    return match ($zoneId) {
                        809 => [['price' => 25.00]],
                        811 => [['price' => 30.00]],
                        default => [],
                    };
                }
            );

        $sut->expects($this->once())
            ->method('getSeatFeesForZone')
            ->with(15024, 809)
            ->willReturn(4.50);

        $this->assertSame(29.50, $sut->getTicketsStartAt(15024));
    }

    private function makeZoneAvailability(int $zoneId, int $availableCount): PerformanceZoneAvailability
    {
        return new PerformanceZoneAvailability([
            'availableCount' => $availableCount,
            'zone'           => [
                'Id'               => $zoneId,
                'Description'      => "Zone {$zoneId}",
                'ShortDescription' => "Z{$zoneId}",
                'Rank'             => 1,
                'ZoneMapId'        => 1,
                'ZoneTime'         => '',
                'Abbreviation'     => "Z{$zoneId}",
                'ZoneLegend'       => 'A',
                'ZoneGroup'        => [],
            ],
        ]);
    }
}
