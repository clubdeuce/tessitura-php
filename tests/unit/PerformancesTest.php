<?php

namespace Clubdeuce\Tessitura\Tests;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Resources\Performance;
use Clubdeuce\Tessitura\Resources\Performances;
use PHPUnit\Framework\Attributes\CoversClass;
use Clubdeuce\Tessitura\Helpers\Api;
use PHPUnit\Framework\Attributes\UsesClass;
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
            ->willReturn(new stdClass());

        $sut      = new Performances($api);
        $upcoming = @$sut->get_performances_for_production_season(35);

        $this->assertIsArray($upcoming);
        $this->assertEmpty($upcoming);
    }
}