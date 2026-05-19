<?php

declare(strict_types=1);

namespace App\State\Order\Provider;

use App\Dto\DialogModalDto;
use App\Entity\OrderHeader;
use App\Mapper\DestructiveModalMapper;


class OrderDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(OrderHeader $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(
            sprintf('Etes vous certain de supprimer la commande  %s ?', $entity->getId())
        );
    }
}