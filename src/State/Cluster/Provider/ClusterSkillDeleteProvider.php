<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Dto\Payload\ClusterSkillDto;
use App\Dto\View\DialogModalView;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class ClusterSkillDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    /**
     * @implements FormComponentProviderInterface<ClusterSkillDto>
     */
    public function mapToView(object $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'évaluation <b>%s</b> ?',
            $entity->skill->getContent()
        ));
    }
}
