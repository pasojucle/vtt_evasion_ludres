<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\Common\Collections\Collection;

class OrderLinesViewModel extends AbstractViewModel
{
    public ?array $lines = [];

    public static function fromOrderLines(collection $orderLines, UserViewModel $orderUser, ServicesPresenter $services)
    {
        $linesView = new self();
        if (!$orderLines->isEmpty()) {
            foreach ($orderLines as $line) {
                $linesView->lines[] = OrderLineViewModel::fromOrderLine($line, $orderUser, $services);
            }
        }

        return $linesView;
    }
}
