<?php

declare(strict_types=1);

namespace App\State\Link\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Link;
use App\Mapper\DestructiveModalMapper;

class LinkDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Link  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer le lien  <b>%s</b> ?', $entity->getTitle()));
    }
}