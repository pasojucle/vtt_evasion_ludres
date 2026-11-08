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
class MessageUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        $action = $data->getId() ? 'Modifier' : 'Ajouter';
        return new SheetView(
            title: sprintf('%s un message', $action),
            description: sprintf('%s un nouveau message en définissant le nom, la désignation, le contenu, le type de niveau éventuel lié et la section', $action),
            action: $action,
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [];
    }
}
