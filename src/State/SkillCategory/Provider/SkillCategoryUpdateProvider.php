<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Provider;

use App\Dto\View\SheetView;
use App\Entity\SkillCategory;
use App\State\Interface\FormComponentProviderInterface;

class SkillCategoryUpdateProvider implements FormComponentProviderInterface
{
    /**
      * @implements FormComponentProviderInterface<SkillCategory>
      */
    public function mapToView(object $entity): SheetView
    {
        return new SheetView(
            title: 'Catégories',
            description: 'Catégories de compétence',
            action: 'Modifier',
        );
    }
}
