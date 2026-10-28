<?php

declare(strict_types=1);

namespace App\UseCase\v2\BoardRole;

use App\Entity\BoardRole;
use App\Repository\Interface\BoardRoleRepositoryInterface;
use App\Service\SoftDeleteService;

class RestoreBoardRole
{
    public function __construct(
        private BoardRoleRepositoryInterface $boardRoleRepository,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    public function __invoke(BoardRole $boardRole): void
    {
        $this->softDeleteService->restore($boardRole);
        
        $this->boardRoleRepository->save($boardRole);
    }
}
