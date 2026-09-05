<?php

declare(strict_types=1);

namespace App\Mapper\User\Read;


use App\Dto\View\User\Tab\SkillsView;
use App\Entity\User;

class SkillsMapper
{
    public function mapToView(User $entity): SkillsView
    {

        return new SkillsView(
            id: $entity->getId(),
        );
    }
}
