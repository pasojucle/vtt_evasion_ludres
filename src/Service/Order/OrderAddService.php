<?php

namespace App\Service\Order;

use DateTime;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use App\Service\UploadService;
use Symfony\Component\Form\Form;
use App\Repository\OrderRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderHeaderRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class OrderAddService
{
    private UploadService $uploadService;
    private EntityManagerInterface $entityManager;
    private OrderHeaderRepository $orderHeaderRepository;
    private Security $security;

    public function __construct(
        UploadService $uploadService,
        EntityManagerInterface $entityManager,
        OrderHeaderRepository $orderHeaderRepository,
        Security $security
    )
    {
        $this->uploadService = $uploadService;
        $this->entityManager =$entityManager;
        $this->orderHeaderRepository = $orderHeaderRepository;
        $this->security = $security;
    }
    public function execute(Product $product, Form &$form)
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
        $orderHeader = $this->orderHeaderRepository->findOneOrderByUserAndStatus($user, OrderHeader::STATUS_VALIDED);
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
                    dump($line);
                    return $line;
                }
            }
        }

        $orderLine->setOrderHeader($orderHeader);
        return $orderLine;
    }
}