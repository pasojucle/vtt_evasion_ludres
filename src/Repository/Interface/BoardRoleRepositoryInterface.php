<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\BoardRole;

interface BoardRoleRepositoryInterface
{
    public function save(BoardRole $boardRole, bool $flush = true): void;

    public function remove(BoardRole $boardRole, bool $flush = true): void;

    public function findAllOrdered(): array;

    public function findNexOrder(): int;
}
