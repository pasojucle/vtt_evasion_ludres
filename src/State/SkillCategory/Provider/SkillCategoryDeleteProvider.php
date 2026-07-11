<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\SkillCategory;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class SkillCategoryDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        /** @var SkillCategory $entity */
        return $this->destructiveModalMapper->mapToView(
            sprintf(
                'Etes vous certain de supprimer la compétence <b>%s</b> ?',
                $entity->getName()
            )
        );
    }
}
