<?php

declare(strict_types=1);

namespace App\State\RegistrationStep\Provider;

use App\Dto\DialogModalDto;
use App\Entity\RegistrationStep;
use App\Mapper\DestructiveModalMapper;

class RegistrationStepDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(RegistrationStep  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer l\'étape <b>%s</b> ?', $entity->getTitle()));
    }
}