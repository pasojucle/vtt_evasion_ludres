<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;
use App\Entity\Product;
use App\State\FormComponentProviderInterface;

class ProductToggleProvider implements FormComponentProviderInterface
{
    /**
    * @implements FormComponentProviderInterface<Product>
    */
    public function mapToView(object $entity): DialogModalView
    {
        if ($entity->isDisabled()) {
            return new DialogModalView(
                type: DialogType::SUCCESS,
                title: 'Activation',
                action: 'Activer',
                message: sprintf('Etes vous certain d\'activer l\'article <b>%s</b> ?', $entity->getName()),
                icon: 'lucide:square-check-big'
            );
        }

        return new DialogModalView(
            type: DialogType::WARNING,
            title: 'Désactivation',
            action: 'Désactiver',
            message: sprintf('Etes vous certain de désactiver l\'article <b>%s</b> ?', $entity->getName()),
            icon: 'lucide:x'
        );
    }
}
