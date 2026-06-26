<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Survey;
use App\Mapper\DestructiveModalMapper;
use App\State\FormComponentProviderInterface;

class SurveyDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        assert($entity instanceof Survey);
        
        return $this->destructiveModalMapper->mapToView(
            sprintf('<p>Toutes les données relative à ce vote seront supprimées.</p><p>Etes-vous certain de supprimer le vote %s ?</p>', $entity->getTitle()),
        );
    }
}
