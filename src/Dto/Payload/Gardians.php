<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\MemberGardian;

class Gardians
{
    /**
     * @param MemberGardian[] $gardians
     */
    public function __construct(
        public array $gardians,
    ) {
    }
}
