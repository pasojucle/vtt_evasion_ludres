<?php

declare(strict_types=1);

namespace App\State\Order\Processor;

use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use Doctrine\ORM\EntityManagerInterface;

class OrderDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(OrderHeader $entity): void
    {
        $entity->setStatus(OrderStatusEnum::CANCELED);

        $this->entityManager->flush();
    }
}