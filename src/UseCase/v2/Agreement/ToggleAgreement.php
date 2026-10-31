<?php

declare(strict_types=1);

namespace App\UseCase\v2\Agreement;

use App\Entity\Agreement;
use App\Repository\Interface\AgreementRepositoryInterface;
use App\Service\DisableService;

class ToggleAgreement
{
    public function __construct(
        private AgreementRepositoryInterface $agreementRepository,
        private DisableService $disableService,
    ) {
    }

    public function __invoke(Agreement $agreement): void
    {
        $this->disableService->toggle($agreement);
        
        $this->agreementRepository->save($agreement);
    }
}
