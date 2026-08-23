<?php

declare(strict_types=1);

namespace App\Dto\Filter;

class RangeChip
{
    public ?string $startValue = null;
    public ?string $endValue = null;

    public array $queries = [];

    public function __construct(
        public string $name,
        public string $formatLabel,
        public string $startField,
        public string $endField,
        public bool $required = false,
    ) {
    }

    public function setValue(string $name, string $value): void
    {
        $valueName = ($name === $this->startField) ? 'startValue' : 'endValue';
        $this->$valueName = $value;
    }

    public function isComplete(): bool
    {
        return null !== $this->startValue && null !== $this->endValue;
    }
}
