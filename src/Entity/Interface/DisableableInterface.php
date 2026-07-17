<?php

declare(strict_types=1);

namespace App\Entity\Interface;

use App\Entity\User;
use DateTimeInterface;

interface DisableableInterface
{
    public function setDisabledAt(?DateTimeInterface $disabledAt): self;

    public function getDisabledAt(): ?DateTimeInterface;

    public function setDisabledBy(?User $disableBy): self;

    public function getDisabledBy(): ?User;

    public function isDisabled(): bool;
}
