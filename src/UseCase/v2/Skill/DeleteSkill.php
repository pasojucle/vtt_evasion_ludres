<?php

declare(strict_types=1);

namespace App\UseCase\v2\Skill;

use App\Entity\Skill;
use App\Repository\Interface\SkillRepositoryInterface;
use App\Service\SoftDeleteService;

class DeleteSkill
{
    public function __construct(
        private SkillRepositoryInterface $skillRepository,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    public function __invoke(Skill $skill): void
    {
        $this->softDeleteService->softDelete($skill);
        
        $this->skillRepository->save($skill);
    }
}
