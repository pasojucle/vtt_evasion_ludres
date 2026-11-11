<?php

declare(strict_types=1);

namespace App\State\Notification\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Notification\UpdateNotification;

class NotificationUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateNotification $updateNotification,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $notification = $payload->data;
        $id = $notification->getId();
        
        ($this->updateNotification)($notification);

        $messageKey = 'notification.flash.success.edit';
        if (!$id) {
            $messageKey = 'notification.flash.success.create';
        }
        
        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
