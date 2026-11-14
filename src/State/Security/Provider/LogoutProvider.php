<?php

declare(strict_types=1);

namespace App\State\Security\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;
use App\Entity\BikeRide;

/**
 * @implements FormComponentProviderInterface<BikeRide>
 */
class LogoutProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return new DialogModalView(
            type: DialogType::DESTRUCTIVE,
            title: 'Déconnection',
            action: 'Se déconnecter',
            message: 'Etes-vous sûr de vouloir vous déconnecter ?',
            icon: 'lucide:log-out'
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ],
        ];
    }
}
