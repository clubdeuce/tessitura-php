<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Resources\ProductionSeason;
use PHPUnit\Framework\Attributes\CoversClass;
use Clubdeuce\Tessitura\Tests\testCase;
use DateTime;

#[CoversClass(ProductionSeason::class)]
class ProductionSeasonTest extends testCase
{
    protected ProductionSeason $sut;

    public function setUp(): void
    {
        $json = [];
        $path = dirname(__DIR__) . '/fixtures/production-season.json';

        if (file_exists($path)) {
            $json = json_decode(file_get_contents($path), 'associative array');
        }

        $this->sut = new ProductionSeason(['response' => $json]);
    }

    public function testFirstPerformance(): void
    {
        try {
            $this->assertInstanceOf(DateTime::class, $this->sut->firstPerformanceDate());
            $this->assertEquals('2024-10-19 7:30 PM', $this->sut->firstPerformanceDate()->format('Y-m-d g:i A'));
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testLastPerformance(): void
    {
        try {
            $this->assertInstanceOf(DateTime::class, $this->sut->lastPerformanceDate());
            $this->assertEquals('2024-10-19 7:30 PM', $this->sut->firstPerformanceDate()->format('Y-m-d g:i A'));
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testFirstPerformanceIsFalse(): void
    {
        $production_season = new ProductionSeason();
        $this->assertFalse($production_season->firstPerformanceDate());
    }

    public function testLastPerformanceIsFalse(): void
    {
        $production_season = new ProductionSeason();
        $this->assertFalse($production_season->lastPerformanceDate());
    }

    public function testFirstPerformanceBadTimezone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->firstPerformanceDate('kjdjfwjnewkf');
    }

    public function testLastPerformanceBadTimezone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->lastPerformanceDate('asdasdsadfadsf');
    }

    public function testPerformances(): void
    {
        $this->assertIsArray($this->sut->performances());
    }
}
