<?php

declare(strict_types=1);

namespace App\State\ActivityType\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\BikeRideType;
use App\Service\UrlContextService;
use App\UseCase\v2\ActivityType\UpdateActivityType;

class ActivityTypeUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateActivityType $updateActivityType,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /** @var BikeRideType $activityType*/
        $activityType = $payload->data;
        $id = $activityType->getId();
        ($this->updateActivityType)($activityType);

        $messageKey = 'activity_type.flash.success.edit';
        if (!$id) {
            $messageKey = 'activity_type.flash.success.create';
        }

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
