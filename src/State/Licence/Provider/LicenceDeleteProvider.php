<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Licence;
use App\Mapper\DestructiveModalMapper;

class LicenceDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Licence  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer l\'inscription de <b>%s</b> ?', $entity->getMember->getIdentity()->getFullName()));
    }
}