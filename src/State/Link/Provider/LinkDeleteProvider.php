<?php

declare(strict_types=1);

namespace App\State\Link\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Link;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class LinkDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity, ?string $fallback = null): DialogModalView
    {
        /** @var Link $entity */
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer le lien  <b>%s</b> ?',
            $entity->getTitle()
        ));
    }
}
