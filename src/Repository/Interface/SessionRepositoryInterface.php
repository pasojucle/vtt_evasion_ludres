<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Session;

interface SessionRepositoryInterface
{
    public function save(Session $session, bool $flush = true): void;

    public function remove(Session $session, bool $flush = true): void;
}