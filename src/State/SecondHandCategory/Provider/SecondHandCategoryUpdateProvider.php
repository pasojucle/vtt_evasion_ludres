<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetView;
use App\Entity\SecondHandCategory;

/**
 * @implements FormComponentProviderInterface<SecondHandCategory>
 */
class SecondHandCategoryUpdateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        $action = $data->getId() ? 'Modifier' : 'Ajouter';
        return new SheetView(
            title: 'Catégorie',
            description: sprintf('%s une catégorie d\'annonce d\'occasion.', $action),
            action: $action,
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [];
    }
}
