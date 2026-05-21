<?php

declare(strict_types=1);

namespace App\State\RegistrationStep\Processor;

use App\Entity\RegistrationStep;
use App\Repository\RegistrationStepRepository;
use App\Service\OrderByService;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationStepDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RegistrationStepRepository $registrationStepRepository,
        private OrderByService $orderByService,
    ) {}

    public function process(RegistrationStep $entity): void
    {
        $group = $entity->getRegistrationStepGroup();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $registrationSteps = $this->registrationStepRepository->findByGroup($group->getId());
        $this->orderByService->ResetOrders($registrationSteps);
    }
}