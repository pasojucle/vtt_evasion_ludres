<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Message;

interface MessageRepositoryInterface
{
    public function save(Message $message, bool $flush = true): void;

    public function remove(Message $message, bool $flush = true): void;
}
