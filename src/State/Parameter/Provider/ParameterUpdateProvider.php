<?php

declare(strict_types=1);

namespace App\State\Parameter\Provider;

use App\Dto\View\SheetView;
use App\Entity\Parameter;
use App\State\FormComponentProviderInterface;

class ParameterUpdateProvider implements FormComponentProviderInterface
{
    public function mapToView(object $entity): SheetView
    {
        /** @var Parameter $entity */

        return new SheetView(
            title: 'Modifier un paramètre',
            description: $entity->getLabel(),
            action: 'Modifier',
        );
    }
}
