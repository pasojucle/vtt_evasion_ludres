<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;

readonly class BadgeDto
{
    public function __construct(
        public string $value,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public Size $size = Size::SM,
    ) {
    }
}
