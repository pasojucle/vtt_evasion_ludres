<?php

declare(strict_types=1);

namespace App\Entity\Interface;

use App\Entity\User;

interface SoftDeletableInterface
{
    public function setDeletedAt(?\DateTimeInterface $deletedAt): self;
    public function setDeletedBy(?User $deletedBy): self;
}