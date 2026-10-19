<?php

declare(strict_types=1);

namespace App\State\Summary\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\Summary;
use App\Mapper\Activity\ActivityPeriodMapper;

/**
 * @implements FormComponentProviderInterface<Summary>
 */
class SummaryUpdateProvider implements FormComponentProviderInterface
{
    public function __construct(
        private ActivityPeriodMapper $activityPeriodMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: sprintf(
                'Modifier une actualité à l\'activité %s du %s',
                $data->getTitle(),
                $this->activityPeriodMapper->mapToView($data->getBikeRide())
            ),
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ]
        ];
    }
}
