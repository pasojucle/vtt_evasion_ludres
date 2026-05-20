<?php

declare(strict_types=1);

namespace App\State\Summary\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Summary;
use App\Mapper\DestructiveModalMapper;

class SummaryDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Summary  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer l\'actualité <b>%s</b> ?', $entity->getTitle()));
    }
}