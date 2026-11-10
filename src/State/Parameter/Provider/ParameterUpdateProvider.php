<?php

declare(strict_types=1);

namespace App\State\Parameter\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\Parameter;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;

/**
 * @implements FormComponentProviderInterface<Parameter>
 */
class ParameterUpdateProvider implements FormComponentProviderInterface
{
    public function __construct(
        private ReplaceKeywordsService $replaceKeywords,
        private SeasonService $seasonService,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier un paramètre',
            description: $this->replaceKeywords->replaceCurrentSaison($data->getLabel(), $this->seasonService->getCurrentSeason()),
            action: 'Modifier',
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [];
    }
}
