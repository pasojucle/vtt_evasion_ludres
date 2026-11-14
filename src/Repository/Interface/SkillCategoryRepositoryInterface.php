<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\SkillCategory;

interface SkillCategoryRepositoryInterface
{
    public function save(SkillCategory $skillCategory, bool $flush = true): void;

    public function remove(SkillCategory $skillCategory, bool $flush = true): void;
}
