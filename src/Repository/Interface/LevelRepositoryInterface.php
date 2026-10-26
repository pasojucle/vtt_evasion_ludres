<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Enum\LevelType;
use App\Entity\Level;

interface LevelRepositoryInterface
{
    public function save(Level $level, bool $flush = true): void;

    public function remove(Level $level, bool $flush = true): void;

    public function findNexOrderByType(LevelType $levelType): int;

    public function findByType(LevelType $levelType): array;
}
