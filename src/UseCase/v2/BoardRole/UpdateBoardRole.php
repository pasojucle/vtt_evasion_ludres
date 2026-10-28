<?php

declare(strict_types=1);

namespace App\UseCase\v2\BoardRole;

use App\Entity\BoardRole;
use App\Repository\Interface\BoardRoleRepositoryInterface;

class UpdateBoardRole
{
    public function __construct(
        private BoardRoleRepositoryInterface $boardRoleRepository,
    ) {
    }

    public function __invoke(BoardRole $boardRole): void
    {
        if (null === $boardRole->getOrderBy()) {
            $order = $this->boardRoleRepository->findNexOrder();
            $boardRole->setOrderBy($order);
        }
        
        $this->boardRoleRepository->save($boardRole);
    }
}
