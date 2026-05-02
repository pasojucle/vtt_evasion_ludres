<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;


readonly class ListBadgesItemDto
{
    public function __construct(
        public string $value,
        public ColorVariant $variant = ColorVariant::DEFAULT,
    ) {

    }
}