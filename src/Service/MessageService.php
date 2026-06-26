<?php

declare(strict_types=1);

namespace App\Service;

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

    public function getMessagesBySection(string $section): array
    {
        $messages = [];
        
        /** @var Message $message */
        foreach ($this->messageRepository->findBySectionNameAndQuery($section) as $message) {
            $messages[] = [
                'id' => $message->getId(),
                'label' => $this->replaceKeywords->replaceCurrentSaison($message->getLabel()),
            ];
        };

        return $messages;
    }

    public function getMessageById(string $id, ?Member $user = null): string|bool|array|int|null
    {
        $message = $this->messageRepository->findOneById($id);

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
