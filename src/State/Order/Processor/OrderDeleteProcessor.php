<?php

declare(strict_types=1);

namespace App\State\Order\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class OrderDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var OrderHeader $entity */
        $entity->setStatus(OrderStatusEnum::CANCELED);

        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'order.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
