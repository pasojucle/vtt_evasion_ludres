<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class OrderByService
{
    private EntityManagerInterface $entityManager;

    public function __construct(

        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function setNewOrders($current, ?array $entities, int $newOrder)
    {dump($current, $entities, $newOrder);
        $oldOrder = $current->getOrderBy();
        if (null !== $newOrder && null !== $entities) {           
            $startOrder = $oldOrder;
            $endOrder = $newOrder;
            $order = $startOrder;

            if (0 < $oldOrder - $newOrder) {
                $startOrder = $newOrder;
                $endOrder = $oldOrder;
                $order = $startOrder;
                ++$order;
            }

            foreach($entities as $entity) {
                if ($entity !== $current && $startOrder <= $entity->getOrderBy() && $entity->getOrderBy() <= $endOrder ) {
                    $entity->setOrderBy($order);
                    ++$order;
                }
            }
            $current->setOrderBy($newOrder);
            $this->entityManager->flush();
        }
    }

    public function ResetOrders(?array $entities)
    {
        if (null !== $entities) {           
            $order = 0;
            foreach($entities as $entity) {
                    $entity->setOrderBy($order);
                    ++$order;
            }

            $this->entityManager->flush();
        }
    }
}