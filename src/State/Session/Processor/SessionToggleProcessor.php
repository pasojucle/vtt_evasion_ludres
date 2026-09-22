<?php

declare(strict_types=1);

namespace App\State\Session\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Service\CsrfTokenService;
use App\UseCase\v2\Session\TogglePresenceSession;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class SessionToggleProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
        private TogglePresenceSession $togglePresenceSession,
    ) {
    }
    

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $session = $payload->data;
        $tokenId = $this->csrfTokenService->getTokenId($session);
        $csrfToken = new CsrfToken($tokenId, $payload->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            return new TurboStreamProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
            );
        }

        ($this->togglePresenceSession)($session);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: $session->isPresent() ? 'session.flash.success.toogle.present' : 'session.flash.success.missing',
            flashType: 'success',
            data: $session->getCluster(),
        );
    }
}
