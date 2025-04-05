<?php

use Clubdeuce\Tessitura\Resources\Performance;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Performance::class)]
class PerformanceTest extends testCase
{
    protected Performance $_sut;

    public function setUp(): void
    {
        $json = file_get_contents(dirname(__DIR__) . '/fixtures/performance.json');
        $this->_sut = new Performance(json_decode($json, 'associative array'));
    }

    public function testPerformanceId(): void
    {
        $this->assertEquals(4849, $this->_sut->id());
    }

    public function testTitle(): void
    {
        $this->assertIsString($this->_sut->title());
        $this->assertEquals('La Traviata', $this->_sut->title());
    }

    public function testDate(): void
    {
        try {
            $this->assertInstanceOf(DateTime::class, $this->_sut->date());
            $this->assertEquals('2024-10-19 7:30 pm', $this->_sut->date()->format('Y-m-d g:i a'));
            $this->assertEquals('2024-10-19 7:30 pm', $this->_sut->date('America/Los_Angeles')->format('Y-m-d g:i a'));
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testProductionSeasonId(): void
    {
        $this->assertIsInt($this->_sut->productionSeasonId());
        $this->assertEquals(4848, $this->_sut->productionSeasonId());
    }

    public function testDateIsNull(): void
    {
        $performance = new Performance();
        $this->assertNull($performance->date());
    }
}