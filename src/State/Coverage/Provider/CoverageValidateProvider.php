<?php

declare(strict_types=1);

namespace App\State\Coverage\Provider;

use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;
use App\Entity\Licence;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<Licence>
 */
class CoverageValidateProvider implements FormComponentProviderInterface
{
    public function getFormView(object $entity, ?string $fallback = null): DialogModalView
    {
        return new DialogModalView(
            type: DialogType::SUCCESS,
            title: 'Mon titre',
            action: 'Action',
            message: sprintf('Confirmez-vous la validation de l\'assurance %s', $entity->getMember()->getIdentity()->getFullName()),
            icon: 'lucide:square-check-big'
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }
}
