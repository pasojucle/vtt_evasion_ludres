<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Background;

interface BackgroundRepositoryInterface
{
    public function save(Background $background, bool $flush = true): void;

    public function remove(Background $background, bool $flush = true): void;
}
