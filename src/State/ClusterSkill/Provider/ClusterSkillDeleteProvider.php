<?php

declare(strict_types=1);

namespace App\State\ClusterSkill\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Payload\AssociateResourcePayload;
use App\Dto\View\DialogModalView;
use App\Mapper\DestructiveModalMapper;

/**
 * @implements FormComponentProviderInterface<AssociateResourcePayload>
 */
class ClusterSkillDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'évaluation <b>%s</b> ?',
            $data->data->getContent()
        ));
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ]
        ];
    }
}
