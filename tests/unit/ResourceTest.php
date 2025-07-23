<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Base\Resource;
use Clubdeuce\Tessitura\Resources\Season;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Resource::class)]
class ResourceTest extends testCase
{
    /**
     * @var Resource
     */
    protected Resource $sut;

    public function setUp(): void
    {
        $data = json_decode(
            file_get_contents(dirname(__DIR__) . '/fixtures/season.json'),
            true
        );

        $this->sut = new Resource($data);
    }

    public function testGetId(): void
    {
        $this->assertIsString($this->sut->getId());
        $this->assertEquals(194, $this->sut->getId());
    }

    public function testGetDescription(): void
    {
        $this->assertIsString($this->sut->getDescription());
        $this->assertEquals('2024/2025 Opera', $this->sut->getDescription());
    }

    public function testSetId(): void
    {
        $this->sut->setId('12345');
        $this->assertEquals('12345', $this->sut->getId());
    }

    public function testSetDescription(): void
    {
        $this->sut->setDescription('New Description');
        $this->assertEquals('New Description', $this->sut->getDescription());
    }
}
