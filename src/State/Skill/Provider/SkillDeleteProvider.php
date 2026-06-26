<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Skill;
use App\Mapper\DestructiveModalMapper;
use App\State\FormComponentProviderInterface;

class SkillDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        /** @var Skill $entity */
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer la compétence <b>%s</b> ?',
            strip_tags($entity->getContent())
        ));
    }
}
