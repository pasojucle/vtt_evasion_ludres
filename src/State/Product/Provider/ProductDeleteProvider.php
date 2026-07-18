<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Product;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class ProductDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity, ?string $fallback = null): DialogModalView
    {
        /** @var Product $entity */
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer l\'article <b>%s</b> ?', $entity->getName()));
    }
}
