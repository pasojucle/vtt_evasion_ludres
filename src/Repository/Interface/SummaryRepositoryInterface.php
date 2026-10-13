<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Summary;

interface SummaryRepositoryInterface
{
    public function save(Summary $summary, bool $flush = true): void;

    public function remove(Summary $summary, bool $flush = true): void;
}
