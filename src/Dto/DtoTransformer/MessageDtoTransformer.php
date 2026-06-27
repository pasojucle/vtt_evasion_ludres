<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\MessageDto;
use App\Entity\Message;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MessageDtoTransformer
{
    public function __construct(
        private readonly ReplaceKeywordsService $replaceKeywordsService,
        private SeasonService $seasonService,
    ) {
    }

    public function fromEntity(?Message $message): MessageDto
    {
        $messageDto = new MessageDto();
        $messageDto->id = $message->getId();
        $messageDto->label = $this->replaceKeywordsService->replaceCurrentSaison($message->getLabel(), $this->seasonService->getCurrentSeason());
        $messageDto->isProtected = $message->isProtected();
        ;
        return $messageDto;
    }

    public function fromEntities(Paginator $messagesEntities): array
    {
        $messages = [];
        foreach ($messagesEntities as $message) {
            $messages[] = $this->fromEntity($message);
        }

        return $messages;
    }
}
