<?php

declare(strict_types=1);

namespace App\State\Notification\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Service\CsrfTokenService;
use App\UseCase\v2\Notification\ToggleNotification;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class NotificationToggleProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
        private ToggleNotification $toggleNotification,
    ) {
    }
    
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $notification = $payload->data;
        $tokenId = $this->csrfTokenService->getTokenId($notification);
        $csrfToken = new CsrfToken($tokenId, $payload->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            new TurboStreamProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
            );
        }

        ($this->toggleNotification)($notification);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: $notification->isDisabled()
                ? 'notification.flash.success.disabled'
                : 'notification.flash.success.enabled',
            flashType: 'success',
            data: $notification,
        );
    }
}
