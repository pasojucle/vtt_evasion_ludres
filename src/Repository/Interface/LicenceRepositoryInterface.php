<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Licence;

interface LicenceRepositoryInterface
{
    public function save(Licence $session, bool $flush = true): void;

    public function remove(Licence $session, bool $flush = true): void;
}
