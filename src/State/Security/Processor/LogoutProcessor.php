<?php

declare(strict_types=1);

namespace App\State\Security\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UserClearSession;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LogoutProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserClearSession $userClearSession,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $this->userClearSession->execute();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'secutity.logout.success',
            targetUrl: $this->urlGenerator->generate('app_logout'),
        );
    }
}
