<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Product;
use App\Mapper\DestructiveModalMapper;

class ProductDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Product  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer l\'article <b>%s</b> ?', $entity->getName()));
    }
}