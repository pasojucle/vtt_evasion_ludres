<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Enum\OrderLineStateEnum;
use App\Entity\Member;
use App\Entity\OrderLine;
use Doctrine\Common\Collections\Collection;   

class OrderService
{
    public function getAmount(Collection $orderLines, Member $member): string
    {
        $amount = 0;
        /** @var OrderLine $line */
        foreach ($orderLines as $line) {
            if (OrderLineStateEnum::UNAVAILABLE !== $line->getState()) {
                $product = $line->getProduct();
                $price = $product->getCategory() === $member->getLastLicence()->getCategory()
                ? $product->getDiscountPrice()
                : $product->getPrice();
                $amount += $line->getQuantity() * $price;
            }
        }

        return sprintf('%s €', number_format($amount, 2));
    }
}
