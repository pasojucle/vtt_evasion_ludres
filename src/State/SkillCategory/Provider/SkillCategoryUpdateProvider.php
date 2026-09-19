<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Provider;

use App\Dto\State\ViewContext;
use App\Dto\View\SheetView;
use App\Entity\SkillCategory;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<SkillCategory>
*/
class SkillCategoryUpdateProvider implements FormComponentProviderInterface
{
    public function getFormView(object $entity, ?ViewContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Catégories',
            description: 'Catégories de compétence',
            action: 'Modifier',
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }
}
