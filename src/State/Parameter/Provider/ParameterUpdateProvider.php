<?php

declare(strict_types=1);

namespace App\State\Parameter\Provider;

use App\Dto\View\SheetView;
use App\Entity\Parameter;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;
use App\State\Interface\FormComponentProviderInterface;

class ParameterUpdateProvider implements FormComponentProviderInterface
{
    public function __construct(
        private ReplaceKeywordsService $replaceKeywords,
        private SeasonService $seasonService,
    ) {
    }

    public function mapToView(object $entity, ?string $fallback = null): SheetView
    {
        /** @var Parameter $entity */

        return new SheetView(
            title: 'Modifier un paramètre',
            description: $this->replaceKeywords->replaceCurrentSaison($entity->getLabel(), $this->seasonService->getCurrentSeason()),
            action: 'Modifier',
        );
    }
}
