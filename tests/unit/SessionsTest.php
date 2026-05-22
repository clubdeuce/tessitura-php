<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Interfaces\ApiInterface;
use Clubdeuce\Tessitura\Resources\Session;
use Clubdeuce\Tessitura\Resources\Sessions;
use Clubdeuce\Tessitura\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(Sessions::class)]
#[UsesClass(Session::class)]
class SessionsTest extends TestCase
{
    public function testGetByKeyUsesTenMinuteCache(): void
    {
        $api = $this->createMock(ApiInterface::class);
        $api->expects($this->once())
            ->method('get')
            ->with('Web/Session/abc123', ['cache_expiration' => 10 * 60])
            ->willReturn(['LoginInfo' => ['ConstituentId' => 123]]);

        $sut = new Sessions($api);

        $this->assertInstanceOf(Session::class, $sut->getByKey('abc123'));
    }
}
