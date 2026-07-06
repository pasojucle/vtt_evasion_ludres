<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Level;

class LevelOrderDto
{
    public function __construct(
        public Level $level,
        public int $newOrder
    ) {
    }
}
