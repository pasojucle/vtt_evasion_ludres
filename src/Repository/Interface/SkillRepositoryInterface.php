<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Skill;

interface SkillRepositoryInterface
{
    public function save(Skill $skill, bool $flush = true): void;

    public function remove(Skill $skill, bool $flush = true): void;
}
