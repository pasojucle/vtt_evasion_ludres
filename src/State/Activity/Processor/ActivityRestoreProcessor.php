<?php

declare(strict_types=1);

namespace App\State\Activity\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\UseCase\v2\Activity\RestoreActivity;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class ActivityRestoreProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private RestoreActivity $restoreActivity,
    ) {
    }


    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $activity = $payload->data;

        ($this->restoreActivity)($activity);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'activity.flash.success.restaure',
            flashType: 'success',
            data: $activity,
        );
    }
}
