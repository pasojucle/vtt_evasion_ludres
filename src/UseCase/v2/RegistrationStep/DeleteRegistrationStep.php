<?php

declare(strict_types=1);

namespace App\UseCase\v2\RegistrationStep;

use App\Entity\RegistrationStep;
use App\Repository\Interface\RegistrationStepRepositoryInterface;
use App\Service\OrderByService;

class DeleteRegistrationStep
{
    public function __construct(
        private RegistrationStepRepositoryInterface $registrationStepRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function __invoke(RegistrationStep $registrationStep): void
    {
        $group = $registrationStep->getRegistrationStepGroup();

        $this->registrationStepRepository->remove($registrationStep);

        $registrationSteps = $this->registrationStepRepository->findByGroup($group->getId());
        $this->orderByService->resetOrders($registrationSteps);
    }
}
