<?php

namespace Clubdeuce\Tessitura\Tests\unit;

use Clubdeuce\Tessitura\Base\Base;
use Clubdeuce\Tessitura\Tests\testCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Base::class)]
class BaseTest extends testCase
{
    public function testParseArgs(): void
    {
        $sut = new Base();

        $results = $sut->parseArgs(['foo' => 'foobar'], ['foo' => 'bar', 'bar' => 'baz']);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('foo', $results);
        $this->assertArrayHasKey('bar', $results);
        $this->assertEquals('foobar', $results['foo']);
        $this->assertEquals('baz', $results['bar']);
    }
}
