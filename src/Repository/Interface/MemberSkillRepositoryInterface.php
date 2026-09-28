<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\MemberSkill;

interface MemberSkillRepositoryInterface
{
    public function save(MemberSkill $memberSkill, bool $flush = true): void;

    public function remove(MemberSkill $memberSkill, bool $flush = true): void;
}
