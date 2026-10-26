<?php

declare(strict_types=1);

namespace App\UseCase\v2\Skill;

use App\Entity\Skill;
use App\Repository\Interface\SkillRepositoryInterface;

class UpdateSkill
{
    public function __construct(
        private SkillRepositoryInterface $skillRepository,
    ) {
    }

    public function __invoke(Skill $skill): void
    {
        $this->skillRepository->save($skill);
    }
}
