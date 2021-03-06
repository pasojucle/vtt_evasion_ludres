<?php

declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderHeaderRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Security;

class OrderAddService
{
    private EntityManagerInterface $entityManager;

    private OrderHeaderRepository $orderHeaderRepository;

    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        OrderHeaderRepository $orderHeaderRepository,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->orderHeaderRepository = $orderHeaderRepository;
        $this->security = $security;
    }

    public function execute(Product $product, Form &$form): void
    {
        $user = $this->security->getUser();
        $orderLine = $form->getData();
        $orderHeader = $this->getOrderHeader($user);

        $orderLine->setProduct($product);
        $orderLine = $this->setOrderLine($orderHeader, $orderLine);

        if ($form->isValid()) {
            $this->entityManager->persist($orderLine);
            $this->entityManager->flush();
        }
    }

    private function getOrderHeader(User $user): OrderHeader
    {
        $orderHeader = $this->orderHeaderRepository->findOneOrderByUser($user);
        if (null === $orderHeader) {
            $orderHeader = new OrderHeader();
            $orderHeader->setUser($user)
                ->setCreatedAt(new DateTime())
                ->setStatus(OrderHeader::STATUS_IN_PROGRESS)
                ;
            $this->entityManager->persist($orderHeader);
        }

        return $orderHeader;
    }

    private function setOrderLine(OrderHeader $orderHeader, OrderLine $orderLine): OrderLine
    {
        if (!$orderHeader->getOrderLines()->isEmpty()) {
            foreach ($orderHeader->getOrderLines() as $line) {
                if ($line->getProduct() === $orderLine->getProduct() && $line->getSize() === $orderLine->getSize()) {
                    $quantity = $line->getQuantity() + $orderLine->getQuantity();
                    $line->setQuantity($quantity);

                    return $line;
                }
            }
        }

        $orderLine->setOrderHeader($orderHeader);

        return $orderLine;
    }
}
