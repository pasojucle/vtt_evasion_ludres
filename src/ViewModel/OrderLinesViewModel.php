<?php

namespace App\ViewModel;

use App\ViewModel\ProductViewModel;
use Doctrine\Common\Collections\Collection;

class OrderLinesViewModel extends AbstractViewModel
{
    public ?array $lines = [];

    public static function fromOrderLines(collection $orderLines, string $productDirecrtory)
    {
        $linesView = new self();
        if (!$orderLines->isEmpty()) {
            foreach($orderLines as $line) {
                $amount = $line->getQuantity() * $line->getProduct()->getPrice();
                $linesView->lines[] = [
                    'product' => ProductViewModel::fromProduct($line->getProduct(), $productDirecrtory),
                    'quantity' => $line->getQuantity(),
                    'size' => $line->getSize()->getName(),
                    'amount_float' => $amount,
                    'amount' => number_format($amount, 2).' €',
                ];
            }
        }

        return $linesView;
    }
}