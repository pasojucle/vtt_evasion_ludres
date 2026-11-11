<?php

declare(strict_types=1);

namespace App\State\Notification\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\Notification;

/**
 * @implements FormComponentProviderInterface<Notification>
 */
class NotificationUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        $action = ($data->getId()) ? 'Modifier' : 'Ajouter';
        return new SheetView(
            title: sprintf('%s une notification', $action),
            description: sprintf('%s un notification en définissant les paramètres généreaux, les options...', $action),
            action: $action,
            frame: sprintf('notification-update-%s', $data->getId() ?? ''),
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ]
        ];
    }
}
