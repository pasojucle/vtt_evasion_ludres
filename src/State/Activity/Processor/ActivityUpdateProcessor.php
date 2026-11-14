<?php

declare(strict_types=1);

namespace App\State\Activity\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Activity\UpdateActivity;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class ActivityUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateActivity $updateActivity,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $bikeRide = $payload->data;
        $id = $bikeRide->getId();
        ($this->updateActivity)($bikeRide, $payload->files);

        $messageKey = 'activity.flash.success.edit';
        if (!$id) {
            $messageKey = 'activity.flash.success.create';
        }

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
