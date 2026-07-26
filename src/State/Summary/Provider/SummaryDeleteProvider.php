<?php

declare(strict_types=1);

namespace App\State\Summary\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Summary;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<Summary>
 */
class SummaryDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'actualité <b>%s</b> ?',
            $entity->getTitle()
        ));
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }
}
