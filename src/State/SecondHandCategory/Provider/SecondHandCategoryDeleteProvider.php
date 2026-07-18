<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\SecondHandCategory;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class SecondHandCategoryDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity, ?string $fallback = null): DialogModalView
    {
        /** @var SecondHandCategory $entity */
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer la catégorie %s', $entity->getName()));
    }
}
