<?php

declare(strict_types=1);

namespace App\State\Activity\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\BikeRide;
use App\State\Interface\HtmlProcessorInterface;
use App\UseCase\v2\Activity\UpdateActivity;
use Doctrine\ORM\EntityManagerInterface;

class ActivityUpdateProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UpdateActivity $updateActivity
    ) {
    }

    /**
     * @param BikeRide $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): HtmlProcessorResult
    {
        $entity = $this->updateActivity->execute($entity, $uploadFiles);

        $messageKey = 'activity.flash.success.edit';
        if (!$entity->getId()) {
            $this->entityManager->persist($entity);
            $messageKey = 'activity.flash.success.create';
        }
        
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: $messageKey,
        );
    }
}
