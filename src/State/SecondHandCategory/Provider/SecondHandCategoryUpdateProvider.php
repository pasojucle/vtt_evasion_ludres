<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Provider;

use App\Dto\View\SheetView;
use App\Entity\SecondHandCategory;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<SecondHandCategory>
 */
class SecondHandCategoryUpdateProvider implements FormComponentProviderInterface
{
    public function mapToView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Catégorie',
            description: 'Catégorie d\'annonce d\'occasion.',
            action: 'Modifier',
        );
    }
}
