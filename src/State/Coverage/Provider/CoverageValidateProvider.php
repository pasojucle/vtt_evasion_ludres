<?php

declare(strict_types=1);

namespace App\State\Coverage\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;
use App\Entity\Licence;

/**
 * @implements FormComponentProviderInterface<Licence>
 */
class CoverageValidateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return new DialogModalView(
            type: DialogType::SUCCESS,
            title: 'Mon titre',
            action: 'Action',
            message: sprintf('Confirmez-vous la validation de l\'assurance %s', $data->getMember()->getIdentity()->getFullName()),
            icon: 'lucide:square-check-big'
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [];
    }
}
