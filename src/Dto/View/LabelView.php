<?php

declare(strict_types=1);

namespace App\Dto\View;


readonly class LabelView
{
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';

    public function __construct(
        public string $value,
        public string $type = self::TYPE_TEXT,
    ) {
    }
}
