<?php

declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderHeaderRepository;
use App\Service\ModalWindowService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Form;

class OrderAddService
{
    private User $user;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderHeaderRepository $orderHeaderRepository,
        private Security $security,
        private ModalWindowService $modalWindowService
    ) {
    }

    public function execute(Product $product, Form &$form): void
    {
        /** @var User $userConnected */
        $userConnected = $this->security->getUser();
        $this->user = $userConnected;
        $orderLine = $form->getData();
        $orderHeader = $this->getOrderHeader();

        $orderLine->setProduct($product);
        $orderLine = $this->setOrderLine($orderHeader, $orderLine);

        if ($form->isValid()) {
            $this->entityManager->persist($orderLine);
            $this->entityManager->flush();
            $this->modalWindowService->addToModalWindowShowed($orderHeader);
        }
    }

    private function getOrderHeader(): OrderHeader
    {
        $orderHeader = $this->orderHeaderRepository->findOneOrderInProgressByUser($this->user);
        if (null === $orderHeader) {
            $orderHeader = new OrderHeader();
            $orderHeader->setUser($this->user)
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
