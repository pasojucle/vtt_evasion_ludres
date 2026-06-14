<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Provider;

use App\Dto\DialogModalView;
use App\Entity\SkillCategory;
use App\Mapper\DestructiveModalMapper;

class SkillCategoryDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(SkillCategory  $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(
            sprintf(
            'Etes vous certain de supprimer la compétence <b>%s</b> ?',
            $entity->getName()
        )
        );
    }
}
