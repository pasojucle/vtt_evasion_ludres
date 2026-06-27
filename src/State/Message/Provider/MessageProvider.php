<?php

declare(strict_types=1);

namespace App\State\Message\Provider;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;

class MessageProvider
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly ReplaceKeywordsService $replaceKeywords,
        private SeasonService $seasonService,
    ) {
    }

    public function getMessagesBySectionName(string $name): array
    {
        $messages = [];
        
        /** @var Message $message */
        foreach ($this->messageRepository->findBySectionNameAndQuery($name) as $message) {
            $messages[] = [
                'id' => $message->getId(),
                'label' => $this->replaceKeywords->replaceCurrentSaison($message->getLabel(), $this->seasonService->getCurrentSeason()),
            ];
        };

        return $messages;
    }

    public function getMessageById(string $id, ?Member $user = null): ?string
    {
        $message = $this->messageRepository->findOneByid($id);

        if ($message) {
            $content = $message->getContent();
            if ($user) {
                $content = $this->replaceKeywords->replaceUserData($content, $user);
            }

            return $this->replaceKeywords->replaceCurrentSaison($content, $this->seasonService->getCurrentSeason());
        }

        return null;
    }
}
