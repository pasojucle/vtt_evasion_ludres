<?php

declare(strict_types=1);

namespace App\State\BikeRideType\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\BikeRideType;
use App\Service\SoftDeleteService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BikeRideTypeDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @implements HtmlProcessorInterface<BikeRideType>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->softDeleteService->softDelete($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'bike_ride_type.flash.success.delete',
            flashType: 'success',
        );
    }
}
