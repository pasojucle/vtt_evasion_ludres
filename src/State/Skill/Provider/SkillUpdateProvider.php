<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\BikeRide;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class SkillUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        $action = ($data->getId()) ? 'Modifier' : 'Ajouter';

        return new SheetView(
            title: sprintf('%s une compétence', $action),
            description: sprintf('%s une compétence en définissant sa catégorie, son niveau et son contenu', $action),
            action: $action,
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [];
    }
}
