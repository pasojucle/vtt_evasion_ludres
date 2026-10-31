<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Agreement;

interface AgreementRepositoryInterface
{
    public function save(Agreement $agreement, bool $flush = true): void;

    public function remove(Agreement $agreement, bool $flush = true): void;

    public function findNexOrder(): int;

    public function findAllOrdered(): array;
}
