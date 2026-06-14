<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Skill;
use App\Mapper\DestructiveModalMapper;

class SkillDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(Skill  $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer la compétence <b>%s</b> ?',
            $entity->getContent()
        ));
    }
}
