<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;
use App\Entity\Licence;

/**
 * @implements FormComponentProviderInterface<Licence>
 */
class LicenceReceiveProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        $message = ($data->getState()->isYearly())
            ? 'Confirmez-vous la bonne réception du dossier d\'inscription de %s signé avec le paiement?'
            : 'Confirmez-vous la bonne réception du dossier d\'inscription de %s signé';

        return new DialogModalView(
            type: DialogType::SUCCESS,
            title: 'Inscription',
            action: 'Réceptionner',
            message: sprintf($message, $data->getMember()->getIdentity()->getFullName()),
            icon: 'lucide:square-check-big'
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ]
        ];
    }
}
