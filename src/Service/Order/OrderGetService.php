<?php

declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\OrderHeader;
use App\Entity\User;
use App\Repository\OrderHeaderRepository;
use Symfony\Component\Security\Core\Security;

class OrderGetService
{
    private Security $security;

    private OrderHeaderRepository $orderHeaderRepository;

    public function __construct(Security $security, OrderHeaderRepository $orderHeaderRepository)
    {
        $this->security = $security;
        $this->orderHeaderRepository = $orderHeaderRepository;
    }

    public function getOrderByUser(): ?OrderHeader
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if (null === $user) {
            return null;
        }

        return $this->orderHeaderRepository->findOneOrderNotEmpty($user);
    }
}
