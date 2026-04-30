<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;


readonly class ListCellItemDto
{
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_BADGE = 'badge';

    public function __construct(
        public string $value,
        public string $type = self::TYPE_TEXT,
        public ColorVariant $variant = ColorVariant::DEFAULT,
    ) {

    }
}