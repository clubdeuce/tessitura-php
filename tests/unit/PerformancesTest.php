<?php

namespace Clubdeuce\Tessitura\Tests;

use Clubdeuce\Tessitura\Resources\Performance;
use Clubdeuce\Tessitura\Resources\Performances;
use Clubdeuce\Tessitura\Resources\PerformanceZoneAvailability;
use Clubdeuce\Tessitura\Resources\PriceSummary;
use Clubdeuce\Tessitura\Helpers\Api;
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

        $sut = new Performances($api);
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
            ->willReturn(json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performances.json'), 'associative'));

        $sut = new Performances($api);
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
            ->willReturn(json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performances.json'), 'associative'));

        $sut = new Performances($api);

        // the dates here are irrelevant, as we are using a mock api response
        $result = $sut->getPerformancesBetween(new \DateTime(), new \DateTime());

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $previous = $result[array_key_first($result)];

        foreach ($result as $index => $current) {
            $this->assertEquals($index, $current->date()->getTimestamp(), 'The array index is not the performance timestamp.');
            $this->assertGreaterThanOrEqual($previous->date()->getTimestamp(), $index, 'The performance array is not sorted correctly.');
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
            ->willReturn(json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performances.json'), 'associative'));

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
        $zone = $reflection->invokeArgs($sut, [$data[0]]);

        $this->assertInstanceOf(PerformanceZoneAvailability::class, $zone);
    }

    #[Depends('testMakeNewZoneAvailability')]
    public function testGetZoneAvailabilities() {
        try {
            $api = $this->createMock(Api::class);
            $api->method('get')
                ->willReturn(json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performance-zones.json'), true));

            $sut = new Performances($api);
            $result = $sut->getPerformanceZoneAvailabilities(12345);

            $this->assertIsArray($result);
            $this->assertContainsOnly(PerformanceZoneAvailability::class, $result);
        } catch (Exception $e) {
        }
    }

    public function testGetZoneAvailabilitiesError() {
        try{
            $api = $this->createMock(Api::class);
            $api->method('get')
                ->willThrowException(new \Exception('Mock error', 400));

            $sut = new Performances($api);
            $result = $sut->getPerformanceZoneAvailabilities(12345);

            $this->assertIsArray($result);
            $this->assertEmpty($result);
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }
    }

//    public function testGetPricesForPerformance() {
//        try {
//            $api = $this->createMock(Api::class);
//            $api->method('get')
//                ->willReturn(json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performance-prices.json'), true));
//
//            $sut    = new Performances($api);
//            $prices = $sut->getPricesForPerformance(12345);
//
//            $this->assertIsArray($prices);
//            $this->assertNotEmpty($prices);
//            $this->assertContainsOnly(PriceSummary::class, $prices);
//        } catch (Exception $e) {
//            trigger_error($e->getMessage());
//        }
//    }
}