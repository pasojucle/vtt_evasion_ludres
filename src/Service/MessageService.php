<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Entity\Member;
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

    public function getMessageByName(string $name, ?Member $user = null): string|bool|array|int|null
    {
        $message = $this->messageRepository->findOneByName($name);

        if ($message) {
            $content = $message->getContent();
            if ($user) {
                $content = $this->replaceKeywords->replaceUserData($content, $user);
            }

            return $this->replaceKeywords->replaceCurrentSaison($content);
        }

        return null;
    }
}
