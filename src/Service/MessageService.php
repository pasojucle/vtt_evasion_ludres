<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\ReplaceKeywordsService;

class MessageService
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly ReplaceKeywordsService $replaceKeywords
    ) {
    }

    public function getMessagesBySectionName(string $name): array
    {
        $messages = [];
        
        /** @var Message $message */
        foreach ($this->messageRepository->findBySectionNameAndQuery($name) as $message) {
            $messages[] = [
                'id' => $message->getId(),
                'name' => $message->getName(),
                'label' => $this->replaceKeywords->replaceCurrentSaison($message->getLabel()),
            ];
        };

        return $messages;
    }

    public function getMessageByName(string $name, ?UserDto $user = null): string|bool|array|int|null
    {
        $message = $this->messageRepository->findOneByName($name);

        if ($message) {
            if ($user) {
                return $this->replaceKeywords->replaceUserFullName($message->getContent(), $user);
            }

            return $this->replaceKeywords->replaceCurrentSaison($message->getContent());
        }

        return null;
    }
}
