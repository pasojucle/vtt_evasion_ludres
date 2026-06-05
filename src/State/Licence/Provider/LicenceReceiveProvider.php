<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\DialogModalDto;
use App\Dto\Enum\DialogType;
use App\Entity\Licence;
use App\State\DialogProviderInterface;

/**
 * @implements DialogProviderInterface<Licence>
 */
class LicenceReceiveProvider implements DialogProviderInterface
{
    public function mapToView(object $entity): DialogModalDto
    {
        assert($entity instanceof Licence);

        $message = ($entity->getState()->isYearly())
            ? 'Confirmez-vous la bonne réception du dossier d\'inscription de %s signé avec le paiement?'
            : 'Confirmez-vous la bonne réception du dossier d\'inscription de %s signé';

        return new DialogModalDto(
            type: DialogType::SUCCESS,
            title: 'Inscription',
            action: 'Réceptionner',
            message: sprintf($message, $entity->getMember()->getIdentity()->getFullName()),
            icon: 'lucide:square-check-big'
        );
    }
}
