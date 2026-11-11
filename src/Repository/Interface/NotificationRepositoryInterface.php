<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Notification;

interface NotificationRepositoryInterface
{
    public function save(Notification $notification, bool $flush = true): void;

    public function remove(Notification $notification, bool $flush = true): void;
}
