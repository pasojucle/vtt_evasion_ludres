<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Provider;

use App\Dto\View\SheetView;
use App\Entity\SecondHandCategory;
use App\State\FormComponentProviderInterface;

class SecondHandCategoryUpdateProvider implements FormComponentProviderInterface
{
    /**
     * @implements FormComponentProviderInterface<SecondHandCategory>
     */
    public function mapToView(object $entity): SheetView
    {
        return new SheetView(
            title: 'Catégorie',
            description: 'Catégorie d\'annonce d\'occasion.',
            action: 'Modifier',
        );
    }
}
