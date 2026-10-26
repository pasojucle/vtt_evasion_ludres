<?php

declare(strict_types=1);

namespace App\State\Level\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\BikeRide;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class LevelUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        $action = ($data->getId()) ? 'Modifier' : 'Ajouter';

        return new SheetView(
            title: sprintf('%s un niveau', $action),
            description: sprintf('%s un niveau en définissant le titre, contenu, couleur et type', $action),
            action: $action,
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [];
    }
}
