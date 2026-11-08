<?php

declare(strict_types=1);

namespace App\UseCase\v2\Message;

use App\Entity\Message;
use App\Repository\Interface\MessageRepositoryInterface;

class DeleteMessage
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
    ) {
    }

    public function __invoke(Message $message): void
    {
        $this->messageRepository->remove($message);
    }
}
