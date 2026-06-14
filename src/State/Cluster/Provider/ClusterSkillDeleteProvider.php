<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Skill;
use App\Mapper\DestructiveModalMapper;

class ClusterSkillDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(Skill $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer l\'évaluation <b>%s</b> ?', $entity->getContent()));
    }
}
