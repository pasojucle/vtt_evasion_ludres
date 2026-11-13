<?php

declare(strict_types=1);

namespace App\UseCase\v2\Order;

use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Repository\Interface\OrderHeaderRepositoryInterface;

class UpdateOrder
{
    public function __construct(
        private OrderHeaderRepositoryInterface $orderHeaderRepository,
    ) {
    }

    public function __invoke(OrderHeader $order, ?OrderStatusEnum $status = null): void
    {
        if ($status) {
            $order->setStatus($status);
        }
        
        $this->orderHeaderRepository->save($order);
    }
}
