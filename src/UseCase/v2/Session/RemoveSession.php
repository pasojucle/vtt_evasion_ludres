<?php

declare(strict_types=1);

namespace App\UseCase\v2\Session;

use App\Dto\Service\OperationResult;
use App\Entity\Session;
use App\Repository\Interface\SessionRepositoryInterface;

class RemoveSession
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
    ) {
    }

    public function __invoke(
        Session $session,
    ): OperationResult {
        $this->sessionRepository->remove($session);

        return OperationResult::success();
    }
}
