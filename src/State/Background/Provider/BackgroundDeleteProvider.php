<?php

declare(strict_types=1);

namespace App\State\Background\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Background;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<Background>
 */
class BackgroundDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): DialogModalView
    {
        /** @var Background $entity */
        return $this->destructiveModalMapper->mapToView(
            sprintf('Etes vous certain de supprimer l\'image de fond %s', $entity->getFilename()),
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }
}
