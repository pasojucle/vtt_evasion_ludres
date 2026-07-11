<?php

declare(strict_types=1);

namespace App\State\Notification\Processor;

use App\Dto\Payload\NotificationToggleDto;
use App\Dto\State\ComponentProcessorResult;
use App\Mapper\Notification\NotificationStatusMapper;
use App\Service\CsrfTokenService;
use App\Service\DisableService;
use App\State\Interface\ComponentProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class NotificationToggleProcessor implements ComponentProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
        private DisableService $disableService,
        private NotificationStatusMapper $notificationStatusMapper,
    ) {
    }
    
    /**
     * @implements ComponentProcessorInterface<NotificationToggleDto>
     */
    public function process(object $entity): ComponentProcessorResult
    {
        $tokenId = $this->csrfTokenService->getTokenId($entity->notification);
        $csrfToken = new CsrfToken($tokenId, $entity->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            new ComponentProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
            );
        }

        $notification = $entity->notification;
        $this->disableService->toggle($notification);

        $this->entityManager->flush();

        return new ComponentProcessorResult(
            success: true,
            messageKey: $notification->isDisabled() 
                ? 'notification.flash.success.disabled'
                : 'notification.flash.success.enabled',
            flashType: 'success',
            component: $this->notificationStatusMapper->mapToView($notification, $tokenId)
        );
    }
}

