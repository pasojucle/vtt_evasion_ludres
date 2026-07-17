<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;
use App\Entity\Licence;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<Licence>
 */
class LicenceReceiveProvider implements FormComponentProviderInterface
{
    public function mapToView(object $entity): DialogModalView
    {
        $message = ($entity->getState()->isYearly())
            ? 'Confirmez-vous la bonne réception du dossier d\'inscription de %s signé avec le paiement?'
            : 'Confirmez-vous la bonne réception du dossier d\'inscription de %s signé';

        return new DialogModalView(
            type: DialogType::SUCCESS,
            title: 'Inscription',
            action: 'Réceptionner',
            message: sprintf($message, $entity->getMember()->getIdentity()->getFullName()),
            icon: 'lucide:square-check-big'
        );
    }
}
