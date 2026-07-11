<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Notification;

class NotificationToggleDto
{
    public function __construct(
        public Notification $notification,
        public ?string $token
    ) {
    }
}
