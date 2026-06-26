<?php

declare(strict_types=1);

namespace App\State\Order\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class OrderDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var OrderHeader $entity */
        $entity->setStatus(OrderStatusEnum::CANCELED);

        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            messageKey: 'order.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
