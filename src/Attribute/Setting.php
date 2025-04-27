<?php

declare(strict_types=1);

namespace App\Attribute;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class Setting
{
    public function __construct(
        private string $section,
    ) {
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function getIcon(): string
    {
        return 'fas fa-sliders-h';
    }
}
