<?php

namespace App\Service\Order;

use App\Repository\OrderLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class OrderLinesSetService
{
    private EntityManagerInterface $entityManager;
    private OrderLineRepository $orderLineRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        OrderLineRepository $orderLineRepository
    )
    {
        $this->entityManager =$entityManager;
        $this->orderLineRepository = $orderLineRepository;
    }
    public function execute(Request $request): void
    {
        /**@var array order */
        $order = $request->request->get('order');
        if($order && array_key_exists('orderLines', $order) && !empty($order['orderLines'])) {
            foreach ($order['orderLines'] as $line) {
                $orderLine = $this->orderLineRepository->find($line['lineId']);
                if (array_key_exists('quantity', $line)) {
                    $orderLine->setQuantity($line['quantity']);
                }
                if (array_key_exists('remove', $line)) {
                    $this->entityManager->remove($orderLine);
                }
                $this->entityManager->flush();
            }
        }
    }
}