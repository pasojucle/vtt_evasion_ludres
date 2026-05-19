<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Survey;
use App\Mapper\DestructiveModalMapper;


class SurveyDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Survey $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(
            sprintf('<p>Toutes les données relative à ce vote seront supprimées.</p><p>Etes-vous certain de supprimer le vote %s ?</p>', $entity->getTitle()),
        );
    }
}