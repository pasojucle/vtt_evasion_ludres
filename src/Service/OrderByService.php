<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Interface\SortableInterface;

class OrderByService
{
    public function setNewOrders(SortableInterface $current, ?array $entities, int $newOrder)
    {
        if (null === $entities) {
            return;
        }
        $oldOrder = $current->getOrderBy();

        if ($oldOrder === $newOrder) {
            return;
        }

        $oldOrder = $current->getOrderBy();
        if ($oldOrder < $newOrder) {
            foreach ($entities as $entity) {
                if ($entity === $current) {
                    continue;
                }

                $order = $entity->getOrderBy();
                if ($order > $oldOrder && $order <= $newOrder) {
                    $entity->setOrderBy($order - 1);
                }
            }
        } else {
            foreach ($entities as $entity) {
                if ($entity === $current) {
                    continue;
                }

                $order = $entity->getOrderBy();
                if ($order >= $newOrder && $order < $oldOrder) {
                    $entity->setOrderBy($order + 1);
                }
            }
        }

        $current->setOrderBy($newOrder);
    }

    public function resetOrders(?array $entities)
    {
        if (null === $entities) {
            return;
        }

        $order = 0;
        foreach ($entities as $entity) {
            $entity->setOrderBy($order);
            ++$order;
        }
    }
}
