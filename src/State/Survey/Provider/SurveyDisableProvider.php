<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Dto\View\DialogModalView;
use App\Dto\Enum\DialogType;
use App\Entity\Survey;
use App\State\DialogProviderInterface;

class SurveyDisableProvider implements DialogProviderInterface
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
