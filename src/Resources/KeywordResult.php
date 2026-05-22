<?php

namespace Clubdeuce\Tessitura\Resources;

use Clubdeuce\Tessitura\Base\Base;

class KeywordResult extends Base
{
    /**
     * @return mixed[]
     */
    public function keywords(): array
    {
        return $this->extraArgs['Keywords'] ?? [];
    }
}
