<?php

declare(strict_types=1);

namespace App\State\Session\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Service\CsrfTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class SessionToggleProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
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

        $session->setIsPresent(!$session->isPresent());
        // $user = $session->getUser();
        // $licenceService->applyCompleteTrial($user);
        
        // if ($user instanceof Member && !$user->getLastLicence()->getState()->isYearly()) {
        //     $this->sessionService->checkEndTesting($user);
        // }

        $this->entityManager->flush();

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'session.flash.success.toogle',
            flashType: 'success',
            data: $session->getCluster(),
        );
    }
}
