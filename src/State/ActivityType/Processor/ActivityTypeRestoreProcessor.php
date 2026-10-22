<?php

declare(strict_types=1);

namespace App\State\ActivityType\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\BikeRideType;
use App\Service\SoftDeleteService;
use App\UseCase\v2\ActivityType\UpdateActivityType;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class ActivityTypeRestoreProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private UpdateActivityType $updateActivityType,
        private SoftDeleteService $softDeleteService,
    ) {
    }


    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /** @var BikeRideType $activityType */
        $activityType = $payload->data;
        $this->softDeleteService->restore($activityType);
        $activityType = ($this->updateActivityType)($activityType);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'bike_ride_type.flash.success.restaure',
            flashType: 'success',
            data: $activityType,
        );
    }
}
