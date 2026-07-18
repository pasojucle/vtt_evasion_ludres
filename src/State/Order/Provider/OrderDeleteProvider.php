<?php

declare(strict_types=1);

namespace App\State\Order\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\OrderHeader;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class OrderDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity, ?string $fallback = null): DialogModalView
    {
        /** @var OrderHeader $entity */
        return $this->destructiveModalMapper->mapToView(
            sprintf('Etes vous certain de supprimer la commande  %s ?', $entity->getId())
        );
    }
}
