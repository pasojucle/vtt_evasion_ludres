<?php

declare(strict_types=1);

namespace App\Entity\Interface;

interface SortableInterface
{
    public function getOrderBy(): ?int;

    public function setOrderBy(?int $orderBy): self;
}
