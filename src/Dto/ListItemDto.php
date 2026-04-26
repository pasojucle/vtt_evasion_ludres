<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;


class ListItemDto
{
    public array $cells = [];

    public function __construct(
        public ?DropdownDto $dropdown = null,
        public ?string $background = null,
        public ?string $url = null,
        public ?ButtonDto $action = null,
    ) {

    }

    public function addText(string $value): self
    {
        $this->cells[] = ['value' => $value, 'type' => 'text'];

        return $this;
    }

    public function addNumber(string $value): self
    {
        $this->cells[] = ['value' => $value, 'type' => 'number'];

        return $this;
    }

    public function addCurrency(string $value): self
    {
        $this->addNumber(sprintf('%s €', $value));

        return $this;
    }

    public function addBadge(string $value, ColorVariant $variant = ColorVariant::DEFAULT): self
    {
        $this->cells[] = ['value' => $value, 'type' => 'badge', 'variant'=> $variant];

        return $this;
    }
}