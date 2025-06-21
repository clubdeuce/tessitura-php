<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Resources\Performance;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;
use DateTime;

#[CoversClass(Performance::class)]
class PerformanceTest extends testCase
{
    protected Performance $sut;

    public function setUp(): void
    {
        $json = file_get_contents(dirname(__DIR__) . '/fixtures/performance.json');
        $this->sut = new Performance(json_decode($json, true));
    }

    public function testPerformanceId(): void
    {
        $this->assertEquals(4849, $this->sut->id());
    }

    public function testTitle(): void
    {
        $this->assertIsString($this->sut->title());
        $this->assertEquals('La Traviata', $this->sut->title());
    }

    public function testDate(): void
    {
        try {
            $this->assertInstanceOf(DateTime::class, $this->sut->date());
            $this->assertEquals('2024-10-19 7:30 pm', $this->sut->date()->format('Y-m-d g:i a'));
            $this->assertEquals('2024-10-19 7:30 pm', $this->sut->date('America/Los_Angeles')->format('Y-m-d g:i a'));
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testDateSpecified(): void
    {
        $now = new DateTime();
        $sut = new Performance([
            'date' => $now,
        ]);

        $this->assertInstanceOf(DateTime::class, $sut->date());
    }

    public function testProductionSeasonId(): void
    {
        $this->assertIsInt($this->sut->productionSeasonId());
        $this->assertEquals(4848, $this->sut->productionSeasonId());
    }

    public function testDateIsNull(): void
    {
        $performance = new Performance();
        $this->assertNull($performance->date());
    }

    public function testDescription(): void
    {
        $this->assertIsString($this->sut->description());
        $this->assertEquals('La Traviata', $this->sut->description());
    }

    public function testDoorsOpen(): void
    {
        $this->assertInstanceOf(DateTime::class, $this->sut->doorsOpen());
        $this->assertEquals('2024-10-19 4:00 pm', $this->sut->doorsOpen()->format('Y-m-d g:i a'));
    }

    public function testDoorsOpenFailsGracefully(): void
    {
        $sut = new Performance(['DoorsOpen' => 'invalid-date']);
        $this->assertNull($sut->doorsOpen());
    }

    public function testDoorsOpenIsNull(): void
    {
        $sut = new Performance([]);
        $this->assertNull($sut->doorsOpen());
    }

    public function testFacilityId(): void
    {
        $this->assertIsInt($this->sut->facilityId());
    }

    public function testFacilityIdValid(): void
    {
        $sut = new Performance(['Facility' => ['Id' => 123]]);
        $this->assertEquals(123, $sut->facilityId());
    }

    public function testFacilityIdIsZeroWhenMissing(): void
    {
        $sut = new Performance([]);
        $this->assertEquals(0, $sut->facilityId());
    }

    public function testFacilityIdHandlesUnexpectedData(): void
    {
        $sut = new Performance(['Facility' => ['Id' => 'invalid']]);
        $this->assertEquals(0, $sut->facilityId());
    }

    public function testStartTime(): void
    {
        $this->assertInstanceOf(DateTime::class, $this->sut->startTime());
        $this->assertEquals('2024-10-19 7:30 pm', $this->sut->startTime()->format('Y-m-d g:i a'));
    }

    public function testStartTimeWhenValidReturnsCorrectDateTime(): void
    {
        $date = '2025-12-15 6:45 pm';
        $sut = new Performance(['PerformanceDate' => $date]);
        $this->assertInstanceOf(DateTime::class, $sut->startTime());
        $this->assertEquals($date, $sut->startTime()->format('Y-m-d g:i a'));
    }

    public function testStartTimeWhenInvalidDateReturnsNull(): void
    {
        $sut = new Performance(['PerformanceDate' => 'invalid-date']);
        $this->assertNull($sut->startTime());
    }

    public function testStartTimeWhenPerformanceDateIsMissingReturnsNull(): void
    {
        $sut = new Performance([]);
        $this->assertNull($sut->startTime());
    }

    public function testStatusId(): void
    {
        $this->assertIsInt($this->sut->statusId());
    }

    public function testFacilityDescriptionReturnsString(): void
    {
        $this->assertIsString($this->sut->facilityDescription());
    }

    public function testFacilityDescriptionValid(): void
    {
        $sut = new Performance(['Facility' => ['Description' => 'Main Hall']]);
        $this->assertEquals('Main Hall', $sut->facilityDescription());
    }

    public function testFacilityDescriptionHandlesMissingKey(): void
    {
        $sut = new Performance(['Facility' => []]);
        $this->assertEquals(
            '',
            $sut->facilityDescription(),
            'facilityDescription should return an empty string when the key is missing.'
        );
    }
}
