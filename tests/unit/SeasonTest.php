<?php

use Clubdeuce\Tessitura\Resources\Season;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Season::class)]
class SeasonTest extends testCase
{

    /**
     * @var Season
     */
    protected Season $_sut;

    public function setUp(): void
    {
        $data = json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/season.json'), 'associative');
        $this->_sut = new Season($data);
    }

    public function testId(): void
    {
        $this->assertIsInt($this->_sut->id());
        $this->assertEquals(194, $this->_sut->id());
    }

    public function testDescription()
    {
        $this->assertIsString($this->_sut->description());
        $this->assertEquals('2024/2025 Opera', $this->_sut->description());
    }

    public function testCreatedDateTime(): void
    {
        $created = $this->_sut->createdDateTime();

        $this->assertInstanceOf(\DateTime::class, $created);
        $this->assertEquals('2024-01-19', $created->format('Y-m-d'));
    }

    public function testStartDateTime()
    {
        $start = $this->_sut->startDateTime();

        $this->assertInstanceOf(\DateTime::class, $start);
        $this->assertEquals('2024-07-01', $start->format('Y-m-d'));
    }

    public function testEndDateTime()
    {
        $end = $this->_sut->endDateTime();

        $this->assertInstanceOf(\DateTime::class, $end);
        $this->assertEquals('2025-06-30', $end->format('Y-m-d'));
    }

    public function testCreatedDateTimeIsNull(): void
    {
        $season = new Season();
        $this->assertNull($season->createdDateTime());
    }

    public function testStartDateTimeIsNull()
    {
        $season = new Season();
        $this->assertNull($season->startDateTime());
    }

    public function testEndDateTimeIsNull()
    {
        $season = new Season();
        $this->assertNull($season->endDateTime());
    }

    public function testCreatedDateTimeWithInvalidTimezone(): void
    {
        // Using @ to suppress warnings during test
        $created = @$this->_sut->createdDateTime('InvalidTimezone');
        
        $this->assertInstanceOf(\DateTime::class, $created);
        $this->assertEquals('2024-01-19', $created->format('Y-m-d'));
        // Should fall back to server default timezone (UTC in test environment)
        $this->assertEquals('UTC', $created->getTimezone()->getName());
    }

    public function testStartDateTimeWithInvalidTimezone(): void
    {
        // Using @ to suppress warnings during test
        $start = @$this->_sut->startDateTime('InvalidTimezone');
        
        $this->assertInstanceOf(\DateTime::class, $start);
        $this->assertEquals('2024-07-01', $start->format('Y-m-d'));
        // Should fall back to server default timezone (UTC in test environment)
        $this->assertEquals('UTC', $start->getTimezone()->getName());
    }

    public function testEndDateTimeWithInvalidTimezone(): void
    {
        // Using @ to suppress warnings during test
        $end = @$this->_sut->endDateTime('InvalidTimezone');
        
        $this->assertInstanceOf(\DateTime::class, $end);
        $this->assertEquals('2025-06-30', $end->format('Y-m-d'));
        // Should fall back to server default timezone (UTC in test environment)
        $this->assertEquals('UTC', $end->getTimezone()->getName());
    }
}