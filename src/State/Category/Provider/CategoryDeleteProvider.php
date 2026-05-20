<?php

declare(strict_types=1);

namespace App\State\Category\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Category;
use App\Mapper\DestructiveModalMapper;

class CategoryDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Category  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer la catégorie %s', $entity->getName()));
    }
}