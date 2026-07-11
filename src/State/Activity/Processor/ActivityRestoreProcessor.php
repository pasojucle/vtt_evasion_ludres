<?php

declare(strict_types=1);

namespace App\State\Activity\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\BikeRide;
use App\Service\SoftDeleteService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActivityRestoreProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param BikeRide $entity
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'activity.flash.success.restaure',
        );
    }
}
