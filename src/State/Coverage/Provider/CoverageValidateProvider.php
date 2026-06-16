<?php
declare(strict_types=1);

namespace App\State\Coverage\Provider;

use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;
use App\Entity\Coverage;
use App\Entity\Licence;
use App\Mapper\DestructiveModalMapper;
use App\State\DialogProviderInterface;


class CoverageValidateProvider implements DialogProviderInterface
{

    public function mapToView(object $entity): DialogModalView
    {

        // return $this->destructiveModalMapper->mapToView(sprintf('Confirmez-vous la validation de l'assurance', $entity->Valider));
        /** @var Licence $entity */

        return new DialogModalView(
            type: DialogType::SUCCESS,
            title: 'Mon titre',
            action: 'Action',
            message: sprintf('Confirmez-vous la validation de l\'assurance %s', $entity->getMember()->getIdentity()->getFullName()),
            icon: 'lucide:square-check-big'
        );
    }
}