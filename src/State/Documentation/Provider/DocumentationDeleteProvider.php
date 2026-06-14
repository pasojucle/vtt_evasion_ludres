<?php

declare(strict_types=1);

namespace App\State\Documentation\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Documentation;
use App\Mapper\DestructiveModalMapper;

class DocumentationDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(Documentation  $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer la documentation %s', $entity->getName()));
    }
}
