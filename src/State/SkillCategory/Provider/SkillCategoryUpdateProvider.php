<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\SkillCategory;

/**
 * @implements FormComponentProviderInterface<SkillCategory>
*/
class SkillCategoryUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        $action = $data->getId() ? 'Modifier' : 'Ajouter';
        return new SheetView(
            title: 'Catégorie',
            description: sprintf('%s une catégorie d\e compétence.', $action),
            action: $action,
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }
}
