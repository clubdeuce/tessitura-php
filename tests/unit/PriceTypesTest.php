<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Resources\PriceTypes;
use Clubdeuce\Tessitura\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PriceTypes::class)]
class PriceTypesTest extends TestCase
{
    public function testGetUsesTypesLoadedByGetAll(): void
    {
        $api = $this->createMock(ApiInterface::class);
        $api->expects($this->once())
            ->method('get')
            ->with('TXN/PriceTypes')
            ->willReturn([
                ['Id' => 1, 'Description' => 'Adult'],
                ['Id' => 2, 'Description' => 'Student'],
            ]);

        $sut = new PriceTypes($api);

        $sut->getAll();
        $type = $sut->get(1);

        $this->assertSame('Adult', $type->getDescription());
    }
}
