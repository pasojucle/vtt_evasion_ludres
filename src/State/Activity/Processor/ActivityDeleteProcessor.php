<?php

declare(strict_types=1);

namespace App\State\Activity\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\BikeRide;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActivityDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var BikeRide $entity */
        $entity->setDeleted(true);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'activity.flash.success.delete',
        );
    }
}
