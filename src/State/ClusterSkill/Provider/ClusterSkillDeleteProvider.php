<?php

declare(strict_types=1);

namespace App\State\ClusterSkill\Provider;

use App\Dto\Payload\AssociateResourcePayload;
use App\Dto\State\ViewContext;
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
     * @implements FormComponentProviderInterface<AssociateResourcePayload>
     */
    public function getFormView(object $entity, ?ViewContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'évaluation <b>%s</b> ?',
            $entity->data->getContent()
        ));
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }
}
