<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class OrdersViewModel
{
    public ?array $orders = [];

    public static function fromOrders(Paginator|array $orders, ServicesPresenter $services): OrdersViewModel
    {
        $ordersViewModel = [];
        $isEmpty = ($orders instanceof Paginator) ? 0 === $orders->count() : empty($orders);
        if (!$isEmpty) {
            foreach ($orders as $order) {
                $ordersViewModel[] = OrderViewModel::fromOrderHeader($order, $services);
            }
        }

        $ordersView = new self();
        $ordersView->orders = $ordersViewModel;

        return $ordersView;
    }
}
