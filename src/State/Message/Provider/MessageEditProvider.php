<?php

declare(strict_types=1);

namespace App\State\Message\Provider;

use App\Dto\View\SheetView;
use App\Entity\Message;

class MessageEditProvider
{
    public function createSheet(Message $message): SheetView
    {
        return new SheetView(
            title: 'Modifier un message',
            description: $message->getLabel(),
            action: 'Modifier',
        );
    }
}
