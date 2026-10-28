<?php

declare(strict_types=1);

namespace App\UseCase\v2\BoardRole;

use App\Entity\BoardRole;
use App\Repository\Interface\BoardRoleRepositoryInterface;
use App\Service\OrderByService;

class OrderBoardRole
{
    public function __construct(
        private BoardRoleRepositoryInterface $boardRoleRepository,
        private OrderByService $orderByService
    ) {
    }

    public function __invoke(BoardRole $boardRole, int $newOrder): void
    {
        $boardRoles = $this->boardRoleRepository->findAllOrdered();

        $this->orderByService->setNewOrders($boardRole, $boardRoles, $newOrder);

        $this->boardRoleRepository->save($boardRole);
    }
}
