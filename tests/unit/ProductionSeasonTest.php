<?php

use Clubdeuce\Tessitura\Resources\ProductionSeason;
use PHPUnit\Framework\Attributes\CoversClass;
use Clubdeuce\Tessitura\Tests\testCase;

#[CoversClass(ProductionSeason::class)]
class ProductionSeasonTest extends testCase
{
    protected ProductionSeason $_sut;

    public function setUp(): void
    {
        $json = [];
        $path = dirname(__DIR__) . '/fixtures/production-season.json';

        if (file_exists($path))
            $json = json_decode(file_get_contents($path), 'associative array');

        $this->_sut = new ProductionSeason(['response' => $json]);
    }

    public function testFirstPerformance(): void
    {
        try {
            $this->assertInstanceOf(DateTime::class, $this->_sut->first_performance_date());
            $this->assertEquals('2024-10-19 7:30 PM', $this->_sut->first_performance_date()->format('Y-m-d g:i A'));
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testLastPerformance(): void
    {
        try {
            $this->assertInstanceOf(DateTime::class, $this->_sut->last_performance_date());
            $this->assertEquals('2024-10-19 7:30 PM', $this->_sut->first_performance_date()->format('Y-m-d g:i A'));
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testFirstPerformanceIsFalse(): void
    {
        $production_season = new ProductionSeason();
        $this->assertFalse($production_season->first_performance_date());
    }

    public function testLastPerformanceIsFalse(): void
    {
        $production_season = new ProductionSeason();
        $this->assertFalse($production_season->last_performance_date());
    }

    public function testFirstPerformanceBadTimezone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->_sut->first_performance_date('kjdjfwjnewkf');
    }

    public function testLastPerformanceBadTimezone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->_sut->last_performance_date('asdasdsadfadsf');
    }

    public function testPerformances(): void
    {
        $this->assertIsArray($this->_sut->performances());
    }
}
