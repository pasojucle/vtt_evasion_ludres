<?php

declare(strict_types=1);

namespace App\State\Summary\Provider;

use App\Core\Contract\Provider\DataInitializerInterface;
use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\Summary;
use App\Mapper\Activity\ActivityPeriodMapper;
use DateTimeImmutable;

/**
 * @implements FormComponentProviderInterface<Summary>
 */
class SummaryCreateProvider implements FormComponentProviderInterface, DataInitializerInterface
{
    public function __construct(
        private ActivityPeriodMapper $activityPeriodMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Ajouter',
            description: sprintf(
                'Ajouter une actualité à l\'activité %s du %s',
                $data->getTitle(),
                $this->activityPeriodMapper->mapToView($data->getBikeRide())
            ),
            action: 'Ajouter'
        );
    }

    public function initializeData(object $data): object
    {
        $summary = new Summary();
        $summary->setCreatedAt(new DateTimeImmutable())
            ->setBikeRide($data);

        return $summary;
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
