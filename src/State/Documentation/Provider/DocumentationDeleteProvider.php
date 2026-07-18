<?php

declare(strict_types=1);

namespace App\State\Documentation\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Documentation;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class DocumentationDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    /**
     * @implements FormComponentProviderInterface<Documentation>
     */
    public function mapToView(object $entity, ?string $fallback = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer la documentation %s', $entity->getName()));
    }
}
