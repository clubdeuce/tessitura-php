<?php

namespace Clubdeuce\Tessitura\Tests\Unit;

use Clubdeuce\Tessitura\Resources\Session;
use Clubdeuce\Tessitura\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Session::class)]
class SessionTest extends TestCase
{
    public function testIsLoggedInUsesExplicitFlagWhenPresent(): void
    {
        $sut = new Session([
            'LoginInfo' => [
                'ConstituentId' => 123,
                'IsLoggedIn'    => false,
            ],
        ]);

        $this->assertFalse($sut->isLoggedIn());
    }

    public function testIsLoggedInFallsBackToConstituentId(): void
    {
        $sut = new Session([
            'LoginInfo' => [
                'ConstituentId' => 123,
            ],
        ]);

        $this->assertTrue($sut->isLoggedIn());
    }

    public function testIsLoggedInIsFalseWhenNoLoginStateExists(): void
    {
        $sut = new Session([
            'LoginInfo' => [],
        ]);

        $this->assertFalse($sut->isLoggedIn());
    }
}
