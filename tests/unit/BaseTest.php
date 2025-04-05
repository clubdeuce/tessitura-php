<?php

use Clubdeuce\Tessitura\Base\Base;
use PHPUnit\Framework\Attributes\CoversClass;
use tessitura\tests\includes\testCase;

#[CoversClass(Base::class)]
class BaseTest extends testCase
{
    public function testSetState(): void
    {
        $sut = new Base();

        try {
            $reflection = new \ReflectionMethod($sut::class, '_set_state');
            $reflection->invoke($sut, ['foo' => 'bar']);

            $this->assertEquals('bar', $sut->foo());
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }

    public function testCall(): void
    {
        $sut = new Base(['foo' => 'bar']);

        $this->assertIsArray($sut->extra_args());
        $this->assertEquals('bar', $sut->extra_args()['foo']);
        $this->assertEquals('bar', $sut->foo());
        $this->assertFalse($sut->foobar(), 'Base::__call should return false');
    }

    public function testParseArgs(): void
    {
        $sut = new Base();

        $results = $sut->parse_args(['foo' => 'foobar'], ['foo' => 'bar', 'bar' => 'baz']);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('foo', $results);
        $this->assertArrayHasKey('bar', $results);
        $this->assertEquals('foobar', $results['foo']);
        $this->assertEquals('baz', $results['bar']);
    }
}