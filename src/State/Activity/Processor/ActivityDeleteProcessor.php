<?php

declare(strict_types=1);

namespace App\State\Activity\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\BikeRide;
use App\Service\FilterDecoderService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActivityDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilterDecoderService $filterDecoder,
    ) {
    }

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        /** @var BikeRide $entity */
        $entity->setDeleted(true);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetRoute: 'admin_bike_rides',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: 'activity.flash.success.delete',
        );
    }
}
