<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Message;
use App\Repository\MessageRepository;

class MessageService
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
    ) {
    }

    public function getMessagesBySection(string $section): array
    {
        $messages = [];
        
        /** @var Message $message */
        foreach ($this->messageRepository->findBySectionNameAndQuery($section) as $message) {
            $messages[] = [
                'id' => $message->getId(),
                'label' => $message->getLabel(),
            ];
        };

        return $messages;
    }

    public function getMessageById(string $id): string|null
    {
        $message = $this->messageRepository->findOneById($id);

        if ($message) {
            return $message->getContent();
        }

        return null;
    }
}
