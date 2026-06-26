<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;
use App\Entity\Survey;
use App\State\FormComponentProviderInterface;

class SurveyDisableProvider implements FormComponentProviderInterface
{
    public function mapToView(object $entity): DialogModalView
    {
        /** @var Survey $entity */
        return new DialogModalView(
            type: DialogType::WARNING,
            title: 'Désactivation',
            action: 'Désactiver',
            message: sprintf('<p>Toutes les données relative à ce vote seront supprimées.</p><p>Etes-vous certain de supprimer le vote %s ?</p>', $entity->getTitle()),
            icon: 'lucide:x'
        );
    }
}
