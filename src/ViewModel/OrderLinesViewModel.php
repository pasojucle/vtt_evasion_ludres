<?php

namespace App\ViewModel;

use App\ViewModel\ProductViewModel;
use Doctrine\Common\Collections\Collection;

class OrderLinesViewModel extends AbstractViewModel
{
    public ?array $lines = [];

    public static function fromOrderLines(collection $orderLines, array $services)
    {
        $linesView = new self();
        if (!$orderLines->isEmpty()) {
            foreach($orderLines as $line) {
                $product = ProductViewModel::fromProduct($line->getProduct(),  $services);
                $amount = $line->getQuantity() * $product->sellingPrice;
                $linesView->lines[] = [
                    'product' => $product,
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