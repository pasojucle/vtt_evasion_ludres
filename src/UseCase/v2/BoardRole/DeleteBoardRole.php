<?php

declare(strict_types=1);

namespace App\UseCase\v2\BoardRole;

use App\Entity\BoardRole;
use App\Repository\Interface\BoardRoleRepositoryInterface;
use App\Service\OrderByService;
use App\Service\SoftDeleteService;

class DeleteBoardRole
{
    public function __construct(
        private BoardRoleRepositoryInterface $boardRoleRepository,
        private SoftDeleteService $softDeleteService,
        private OrderByService $orderByService,
    ) {
    }

    public function __invoke(BoardRole $boardRole): void
    {
        $this->softDeleteService->softDelete($boardRole);

        $boardRoles = $this->boardRoleRepository->findAllOrdered();
        $this->orderByService->resetOrders($boardRoles);
        $this->orderByService->setNewOrders($boardRole, $boardRoles, count($boardRoles));
        
        $this->boardRoleRepository->save($boardRole);
    }
}
