<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Documentation;

interface DocumentationRepositoryInterface
{
    public function save(Documentation $documentation, bool $flush = true): void;

    public function remove(Documentation $documentation, bool $flush = true): void;

    public function findAllAsc(): array;
}
