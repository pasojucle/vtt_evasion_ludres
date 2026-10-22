<?php

declare(strict_types=1);

namespace App\State\ActivityType\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\BikeRideType;
use App\Service\SoftDeleteService;
use App\Service\UrlContextService;
use App\UseCase\v2\ActivityType\UpdateActivityType;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class ActivityTypeDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateActivityType $updateActivityType,
        private SoftDeleteService $softDeleteService,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @implements RedirectProcessorInterface<ActionPayload>
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /** @var BikeRideType $data */
        $data = $payload->data;

        $this->softDeleteService->softDelete($data);
        ($this->updateActivityType)($data);

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'bike_ride_type.flash.success.delete',
            flashType: 'success',
        );
    }
}
