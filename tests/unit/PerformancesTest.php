<?php

namespace Clubdeuce\Tessitura\Tests;

use Clubdeuce\Tessitura\Helpers\Api;
use Clubdeuce\Tessitura\Resources\Performance;
use Clubdeuce\Tessitura\Resources\Performances;
use Clubdeuce\Tessitura\Resources\PerformanceZoneAvailability;
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

    /**
     * @throws Exception
     */
    public function testGetSeatFeesForZoneAggregatesFeesForMatchingZone(): void
    {
        $api = $this->createMock(Api::class);
        $api->expects($this->once())
            ->method('get')
            ->willReturn([
                ['ZoneId' => 812, 'FeeAmount' => 2.50],
                ['ZoneId' => 812, 'FeeAmount' => 1.25],
                ['ZoneId' => 813, 'FeeAmount' => 9.99],
            ]);

        $sut = new Performances($api);

        $result = $sut->getSeatFeesForZone(15027, 812);

        $this->assertSame(3.75, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetTicketsStartAtSelectsLowestAvailableZoneAndAddsFees(): void
    {
        $api = $this->createMock(Api::class);
        $api->method('get')
            ->willReturnCallback(
                function (string $endpoint): array {
                    if (str_contains($endpoint, '/Zones?')) {
                        return [
                            [
                                'Id' => 1,
                                'Zone' => ['Id' => 812],
                                'AvailableCount' => 10,
                            ],
                            [
                                'Id' => 2,
                                'Zone' => ['Id' => 813],
                                'AvailableCount' => 5,
                            ],
                            [
                                'Id' => 3,
                                'Zone' => ['Id' => 814],
                                'AvailableCount' => 0,
                            ],
                        ];
                    }

                    if (str_contains($endpoint, '/SeatFees?')) {
                        return [
                            ['ZoneId' => 813, 'FeeAmount' => 4.00],
                        ];
                    }

                    return [
                        ['ZoneId' => 812, 'PriceTypeId' => 1, 'Price' => 40.00, 'IsBase' => true, 'Enabled' => true],
                        ['ZoneId' => 813, 'PriceTypeId' => 1, 'Price' => 30.00, 'IsBase' => true, 'Enabled' => true],
                    ];
                }
            );

        $sut = new Performances($api);

        $result = $sut->getTicketsStartAt(15027);

        $this->assertSame(34.0, $result);
    }
}
