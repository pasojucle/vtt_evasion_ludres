<?php

namespace App\ViewModel;

use App\Service\LicenceService;
use App\ViewModel\OrderViewModel;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OrdersViewModel 
{
    public ?array $orders;

    public static function fromOrders(Paginator $orders, string $productDirectory, LicenceService $licenceService): OrdersViewModel
    {
        $ordersViewModel = [];
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $ordersViewModel[] = OrderViewModel::fromOrderHeader($order, $productDirectory, $licenceService);
            }
        }

        $ordersView = new self();
        $ordersView->orders = $ordersViewModel;

        return $ordersView;
    }
}