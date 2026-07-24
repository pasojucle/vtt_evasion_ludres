<?php

declare(strict_types=1);

namespace App\State\BikeRideType\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\BikeRideType;
use App\Service\SoftDeleteService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BikeRideTypeRestoreProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<BikeRideType>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'bike_ride_type.flash.success.restaure',
            flashType: 'success',
        );
    }
}
