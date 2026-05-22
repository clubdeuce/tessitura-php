<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;

class Session extends Base
{
    public function isLoggedIn(): bool
    {
        $info = $this->loginInfo();
        return 764007 !== ($info['ConstituentId'] ?? 764007);
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
