<?php

declare(strict_types=1);

namespace App\Entity;

class HealthQuestion
{
    private int $field;

    private ?bool $value = null;

    public function getField(): ?int
    {
        return $this->field;
    }

    public function setField(int $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?bool
    {
        return $this->value;
    }

    public function setValue(bool $value): self
    {
        $this->value = $value;

        return $this;
    }
}
