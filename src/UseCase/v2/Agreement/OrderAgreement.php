<?php

declare(strict_types=1);

namespace App\UseCase\v2\Agreement;

use App\Entity\Agreement;
use App\Repository\Interface\AgreementRepositoryInterface;
use App\Service\OrderByService;

class OrderAgreement
{
    public function __construct(
        private AgreementRepositoryInterface $agreementRepository,
        private OrderByService $orderByService
    ) {
    }

    public function __invoke(Agreement $agreement, int $newOrder): void
    {
        $agreements = $this->agreementRepository->findAllOrdered();

        $this->orderByService->setNewOrders($agreement, $agreements, $newOrder);

        $this->agreementRepository->save($agreement);
    }
}
