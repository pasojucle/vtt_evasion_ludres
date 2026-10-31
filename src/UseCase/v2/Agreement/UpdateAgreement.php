<?php

declare(strict_types=1);

namespace App\UseCase\v2\Agreement;

use App\Entity\Agreement;
use App\Repository\Interface\AgreementRepositoryInterface;

class UpdateAgreement
{
    public function __construct(
        private AgreementRepositoryInterface $agreementRepository,
    ) {
    }

    public function __invoke(Agreement $agreement): void
    {
        if (null === $agreement->getOrderBy()) {
            $order = $this->agreementRepository->findNexOrder();
            $agreement->setOrderBy($order);
        }
        
        $this->agreementRepository->save($agreement);
    }
}
