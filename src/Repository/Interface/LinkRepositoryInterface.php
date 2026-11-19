<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Link;

interface LinkRepositoryInterface
{
    public function save(Link $link, bool $flush = true): void;

    public function remove(Link $link, bool $flush = true): void;

    public function findByPosition(int $position): array;
}
