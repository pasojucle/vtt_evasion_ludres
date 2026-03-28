<?php

declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\Member;
use App\Entity\OrderHeader;
use App\Entity\User;
use App\Repository\OrderHeaderRepository;
use Symfony\Bundle\SecurityBundle\Security;

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
        /** @var ?User $member */
        $member = $this->security->getUser();
        if (!$member instanceof Member) {
            return null;
        }

        return $this->orderHeaderRepository->findOneOrderNotEmpty($member);
    }
}
