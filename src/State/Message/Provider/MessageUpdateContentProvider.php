<?php

declare(strict_types=1);

namespace App\State\Message\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\Message;

/**
 * @implements FormComponentProviderInterface<Message>
 */
class MessageUpdateContentProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier le message',
            description: $data->getLabel(),
            action: 'Modifier',
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [];
    }
}
