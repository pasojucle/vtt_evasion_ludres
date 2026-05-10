<?php

declare(strict_types=1);

namespace App\State\Message\Provider;

use App\Dto\SheetDto;
use App\Entity\Message;

class MessageProvider
{
    public function createSheet(Message $message): SheetDto
    {
        return new SheetDto(
            title: 'Modifier un message',
            description: $message->getLabel() ?? '',
            action: 'Modifier',
        );
    }
}