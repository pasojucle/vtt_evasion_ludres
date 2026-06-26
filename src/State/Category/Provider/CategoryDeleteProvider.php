<?php

declare(strict_types=1);

namespace App\State\Category\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Category;
use App\Mapper\DestructiveModalMapper;
use App\State\FormComponentProviderInterface;

class CategoryDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        /** @var Category $entity */
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer la catégorie %s', $entity->getName()));
    }
}
