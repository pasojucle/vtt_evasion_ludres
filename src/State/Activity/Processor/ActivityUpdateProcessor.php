<?php

declare(strict_types=1);

namespace App\State\Activity\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\BikeRide;
use App\Service\UrlContextService;
use App\UseCase\v2\Activity\UpdateActivity;
use Doctrine\ORM\EntityManagerInterface;

class ActivityUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UpdateActivity $updateActivity,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $entity = $this->updateActivity->execute($payload->data, $payload->files);

        $messageKey = 'activity.flash.success.edit';
        if (!$entity->getId()) {
            $this->entityManager->persist($entity);
            $messageKey = 'activity.flash.success.create';
        }
        
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
