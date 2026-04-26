<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Enum\OrderLineStateEnum;
use App\Entity\OrderLine;
use Doctrine\Common\Collections\Collection;   

class OrderService
{
    public function getAmount(Collection $orderLines): string
    {
        $amount = 0;
        /** @var OrderLine $line */
        foreach ($orderLines as $line) {
            if (OrderLineStateEnum::UNAVAILABLE !== $line->getState()) {
                $amount += $line->getQuantity() * $line->getProduct()->getDiscountPrice();
            }
        }

        return number_format($amount, 2);
    }
}
