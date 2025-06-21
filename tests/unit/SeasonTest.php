<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Resources\Season;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Season::class)]
class SeasonTest extends testCase
{
    /**
     * @var Season
     */
    protected Season $sut;

    public function setUp(): void
    {
        $data = json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/season.json'), 'associative');
        $this->sut = new Season($data);
    }

    public function testId(): void
    {
        $this->assertIsInt($this->sut->getId());
        $this->assertEquals(194, $this->sut->getId());
    }

    public function testDescription()
    {
        $this->assertIsString($this->sut->getDescription());
        $this->assertEquals('2024/2025 Opera', $this->sut->getDescription());
    }

    public function testCreatedDateTime(): void
    {
        $created = $this->sut->getCreatedDateTime();

        $this->assertInstanceOf(\DateTime::class, $created);
        $this->assertEquals('2024-01-19', $created->format('Y-m-d'));
    }

    public function testStartDateTime()
    {
        $start = $this->sut->getStartDateTime();

        $this->assertInstanceOf(\DateTime::class, $start);
        $this->assertEquals('2024-07-01', $start->format('Y-m-d'));
    }

    public function testEndDateTime()
    {
        $end = $this->sut->getEndDateTime();

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
