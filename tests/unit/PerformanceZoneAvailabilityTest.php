<?php
namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Resources\PerformanceZoneAvailability as PZA;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PZA::class)]
class PerformanceZoneAvailabilityTest extends testCase
{
    protected PZA $_sut;
    public function setUp(): void
    {
        $response = json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performance-zones.json'), true);
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);

        $this->_sut = new PZA([
            'availableCount' => $response[0]['AvailableCount'],
            'zone'           => $response[0]['Zone']
        ]);
    }

    public function testAvailableCount(): void
    {
        $this->assertEquals(77, $this->_sut->availableCount());
    }

    public function testZone(): void
    {
        $zone = $this->_sut->zone();
        $this->assertIsObject($zone);
        $this->assertObjectHasProperty('id', $zone);
        $this->assertObjectHasProperty('description', $zone);
        $this->assertObjectHasProperty('shortDescription', $zone);
        $this->assertObjectHasProperty('rank', $zone);
        $this->assertObjectHasProperty('zoneMapId', $zone);
        $this->assertObjectHasProperty('zoneTime', $zone);
        $this->assertObjectHasProperty('abbreviation', $zone);
    }
}