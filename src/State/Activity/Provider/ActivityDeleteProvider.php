<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\DialogModalView;
use App\Entity\BikeRide;
use App\Mapper\DestructiveModalMapper;
use App\Service\BikeRideService;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class ActivityDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
        private BikeRideService $bikeRideService,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(
            sprintf(
                '<p>Etes vous certain de supprimer<br>la sortie %s du %s',
                $data->getTitle(),
                $this->bikeRideService->getPeriod($data)
            )
        );
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
