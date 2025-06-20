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
        $this->assertIsInt($this->_sut->getId());
        $this->assertEquals(194, $this->_sut->getId());
    }

    public function testDescription()
    {
        $this->assertIsString($this->_sut->getDescription());
        $this->assertEquals('2024/2025 Opera', $this->_sut->getDescription());
    }

    public function testCreatedDateTime(): void
    {
        $created = $this->_sut->getCreatedDateTime();

        $this->assertInstanceOf(\DateTime::class, $created);
        $this->assertEquals('2024-01-19', $created->format('Y-m-d'));
    }

    public function testStartDateTime()
    {
        $start = $this->_sut->getStartDateTime();

        $this->assertInstanceOf(\DateTime::class, $start);
        $this->assertEquals('2024-07-01', $start->format('Y-m-d'));
    }

    public function testEndDateTime()
    {
        $end = $this->_sut->getEndDateTime();

        $this->assertInstanceOf(\DateTime::class, $end);
        $this->assertEquals('2025-06-30', $end->format('Y-m-d'));
    }

    public function testCreatedDateTimeIsNull(): void
    {
        $season = new Season();
        $this->assertNull($season->getCreatedDateTime());
    }

    public function testStartDateTimeIsNull()
    {
        $season = new Season();
        $this->assertNull($season->getStartDateTime());
    }

    public function testEndDateTimeIsNull()
    {
        $season = new Season();
        $this->assertNull($season->getEndDateTime());
    }
}