<?php

declare(strict_types=1);

namespace App\State\BoardRole\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\BikeRide;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class BoardRoleUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        $action = ($data->getId()) ? 'Modifier' : 'Ajouter';

        return new SheetView(
            title: sprintf('%s un rôle', $action),
            description: sprintf('%s un rôle en définissant le nom', $action),
            action: $action,
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [];
    }
}
