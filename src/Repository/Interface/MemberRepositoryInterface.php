<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Member;

interface MemberRepositoryInterface
{
    public function save(Member $member, bool $flush = true): void;

    public function remove(Member $member, bool $flush = true): void;
}
