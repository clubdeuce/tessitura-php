<?php

namespace unit;

use Clubdeuce\Tessitura\Resources\PriceSummary;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PriceSummary::class)]
class PriceSummaryTest extends testCase
{
    protected PriceSummary $_sut;

    public function setUp(): void
    {
        $data = [];

        if (file_exists(dirname(__DIR__) . '/fixtures/performance-prices.json')) {
            $data = json_decode(file_get_contents(dirname(__DIR__) . '/fixtures/performance-prices.json'), true);
        }

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $this->_sut = new PriceSummary($data[0]);
    }

    public function testPriceSummary()
    {
        $this->assertEquals(809, $this->_sut->zoneId(), 'Zone ID is not 809');
        $this->assertEquals(260, $this->_sut->price(), 'Price is not 260');
    }

    public function testPriceSummaryEnabled()
    {
        $this->assertTrue($this->_sut->enabled());
    }

    public function testPerformanceId()
    {
        $this->assertEquals(15027, $this->_sut->performanceId());
    }
}
