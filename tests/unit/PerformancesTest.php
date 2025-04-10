<?php

namespace Clubdeuce\Tessitura\Tests;

use Clubdeuce\Tessitura\Resources\Performance;
use Clubdeuce\Tessitura\Resources\Performances;
use Clubdeuce\Tessitura\Resources\PerformanceZoneAvailability;
use Clubdeuce\Tessitura\Resources\PriceSummary;
use PHPUnit\Framework\Attributes\CoversClass;
use Clubdeuce\Tessitura\Helpers\Api;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use stdClass;

#[CoversClass(Performances::class)]
#[UsesClass(Performance::class)]
class PerformancesTest extends testCase
{
    public function testSearchReturnsEmptyArray()
    {
        $api = $this->createMock(Api::class);
        $api->method('post')->willReturn([]);

        $sut = new Performances($api);
        $result = $sut->search();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

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

    public function testGetPerformancesBetween()
    {
        $api = $this->createMock(Api::class);
        $api->method('post')
            ->willReturn(json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performances.json'), 'associative'));

        $sut = new Performances($api);

        // the dates here are irrelevant, as we are using a mock api response
        $result = $sut->get_performances_between(new \DateTime(), new \DateTime());

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $previous = $result[array_key_first($result)];

        foreach ($result as $index => $current) {
            $this->assertEquals($index, $current->date()->getTimestamp(), 'The array index is not the performance timestamp.');
            $this->assertGreaterThanOrEqual($previous->date()->getTimestamp(), $index, 'The performance array is not sorted correctly.');
            $previous = $current;
        }

    }


    public function testGetPerformancesForProductionSeason(): void
    {
        $api = $this->createMock(Api::class);
        $api->method('post')
            ->willReturn(json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performances.json'), 'associative'));

        $sut      = new Performances($api);
        $upcoming = $sut->get_performances_for_production_season(35);

        $this->assertIsArray($upcoming);
        $this->assertNotEmpty($upcoming);
    }

    public function testGetPerformancesForProductionSeasonError(): void
    {
        $api = $this->createMock(Api::class);
        $api->method('post')
            ->willReturn([]);

        $sut      = new Performances($api);
        $upcoming = @$sut->get_performances_for_production_season(35);

        $this->assertIsArray($upcoming);
        $this->assertEmpty($upcoming);
    }

    public function testMakeNewZoneAvailability(): void
    {
        $rawData = [];

        if (file_exists(dirname(__DIR__) . '/fixtures/performance-zones.json')) {
            $rawData = file_get_contents(dirname(__DIR__) . '/fixtures/performance-zones.json');
        }

        $data = json_decode($rawData, true);

        $this->assertNotEmpty($data);

        $sut  = new Performances();
        $zone = $sut->makeNewZoneAvailability($data[0]);

        $this->assertInstanceOf(PerformanceZoneAvailability::class, $zone);
    }

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
        }
    }

    public function testGetPricesForPerformance() {
        try {
            $api = $this->createMock(Api::class);
            $api->method('get')
                ->willReturn(json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performance-prices.json'), true));

            $sut    = new Performances($api);
            $prices = $sut->getPricesForPerformance(12345);

            $this->assertIsArray($prices);
            $this->assertNotEmpty($prices);
            $this->assertContainsOnly(PriceSummary::class, $prices);
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }
    }
}