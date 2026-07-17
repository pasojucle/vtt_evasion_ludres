<?php

declare(strict_types=1);

namespace App\Entity\Interface;

use App\Entity\User;
use DateTimeInterface;

interface SoftDeletableInterface
{
    public function setDeletedAt(?DateTimeInterface $deletedAt): self;

    public function getDeletedAt(): ?DateTimeInterface;

    public function setDeletedBy(?User $deletedBy): self;

    public function getDeletedBy(): ?User;

    public function isDeleted(): bool;
}
