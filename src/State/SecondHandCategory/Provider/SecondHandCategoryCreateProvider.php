<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Provider;

use App\Dto\State\ViewContext;
use App\Dto\View\SheetView;
use App\Entity\SecondHandCategory;
use App\State\Interface\FormComponentProviderInterface;

class SecondHandCategoryCreateProvider implements FormComponentProviderInterface
{
    /**
     * @implements FormComponentProviderInterface<SecondHandCategory>
     */
    public function getFormView(object $entity, ?ViewContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Catégorie',
            description: 'Catégorie d\'annonce d\'occasion.',
            action: 'Ajouter',
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }
}
