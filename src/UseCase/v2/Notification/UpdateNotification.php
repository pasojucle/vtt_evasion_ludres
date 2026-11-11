<?php

declare(strict_types=1);

namespace App\UseCase\v2\Notification;

use App\Entity\Notification;
use App\Repository\Interface\NotificationRepositoryInterface;

class UpdateNotification
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
    ) {
    }

    public function __invoke(Notification $notification): void
    {
        $this->notificationRepository->save($notification);
    }
}
