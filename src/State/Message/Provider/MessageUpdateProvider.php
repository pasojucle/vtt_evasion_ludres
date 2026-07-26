<?php

declare(strict_types=1);

namespace App\State\Message\Provider;

use App\Dto\View\SheetView;
use App\Entity\Message;
use App\State\Interface\FormComponentProviderInterface;

class MessageUpdateProvider implements FormComponentProviderInterface
{
    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        /** @var Message $entity */

        return new SheetView(
            title: 'Modifier un message',
            description: $entity->getLabel(),
            action: 'Modifier',
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }
}
