<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Indemnity;

interface IndemnityRepositoryInterface
{
    public function save(Indemnity $indemnity, bool $flush = true): void;

    public function remove(Indemnity $indemnity, bool $flush = true): void;
}
