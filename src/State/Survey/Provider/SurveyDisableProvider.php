<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Dto\DialogModalView;
use App\Dto\Enum\DialogType;
use App\Entity\Survey;
use App\Mapper\DestructiveModalMapper;
use App\State\DialogProviderInterface;

class SurveyDisableProvider implements DialogProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        assert($entity instanceof Survey);
        
        return new DialogModalView(
            type: DialogType::WARNING,
            title: 'Désactivation',
            action: 'Désactiver',
            message: sprintf('<p>Toutes les données relative à ce vote seront supprimées.</p><p>Etes-vous certain de supprimer le vote %s ?</p>', $entity->getTitle()),
            icon: 'lucide:x'
        );
    }
}
