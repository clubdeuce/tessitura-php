<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;

class Session extends Base
{
    public function isLoggedIn(): bool
    {
        $info = $this->loginInfo();

        if (array_key_exists('IsLoggedIn', $info)) {
            return !empty($info['IsLoggedIn']);
        }

        return !empty($info['ConstituentId'] ?? null);
    }

    public function isCartEmpty(): bool
    {
        $cart  = $this->cartInfo();
        $items = [
            'PerformanceCount',
            'PackageCount',
            'ContributionCount',
            'MembershipCount',
            'UserDefinedFeeCount',
            'GiftCertificateCount',
        ];

        foreach ($items as $item) {
            if (!empty($cart[$item])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function loginInfo(): array
    {
        return $this->extraArgs['LoginInfo'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function cartInfo(): array
    {
        return $this->extraArgs['CartInfo'] ?? [];
    }
}
