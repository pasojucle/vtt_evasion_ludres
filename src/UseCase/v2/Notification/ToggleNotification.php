<?php

declare(strict_types=1);

namespace App\UseCase\v2\Notification;

use App\Entity\Notification;
use App\Repository\Interface\NotificationRepositoryInterface;
use App\Service\DisableService;

class ToggleNotification
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private DisableService $disableService,
    ) {
    }

    public function __invoke(Notification $notification): void
    {
        $this->disableService->toggle($notification);
        
        $this->notificationRepository->save($notification);
    }
}
