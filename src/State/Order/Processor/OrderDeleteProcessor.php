<?php

declare(strict_types=1);

namespace App\State\Order\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class OrderDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var OrderHeader $entity */
        $entity->setStatus(OrderStatusEnum::CANCELED);

        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'order.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
