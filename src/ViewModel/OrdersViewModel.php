<?php

namespace App\ViewModel;


use App\ViewModel\OrderViewModel;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OrdersViewModel 
{
    public ?array $orders;

    public static function fromOrders(Paginator $orders, string $productDirectory, int $currentSeason): OrdersViewModel
    {
        $ordersViewModel = [];
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $ordersViewModel[] = OrderViewModel::fromOrderHeader($order, $productDirectory, $currentSeason);
            }
        }

        $ordersView = new self();
        $ordersView->orders = $ordersViewModel;

        return $ordersView;
    }
}