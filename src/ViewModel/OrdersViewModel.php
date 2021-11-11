<?php

namespace App\ViewModel;

use App\ViewModel\OrderViewModel;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OrdersViewModel 
{
    public ?array $orders;

    public static function fromOrders(Paginator $orders, array $services): OrdersViewModel
    {
        $ordersViewModel = [];
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $ordersViewModel[] = OrderViewModel::fromOrderHeader($order,  $services);
            }
        }

        $ordersView = new self();
        $ordersView->orders = $ordersViewModel;

        return $ordersView;
    }
}