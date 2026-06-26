<?php

declare(strict_types=1);

namespace App\State\RegistrationStep\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\RegistrationStep;
use App\Repository\RegistrationStepRepository;
use App\Service\OrderByService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationStepDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RegistrationStepRepository $registrationStepRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var RegistrationStep $entity */
        $group = $entity->getRegistrationStepGroup();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $registrationSteps = $this->registrationStepRepository->findByGroup($group->getId());
        $this->orderByService->resetOrders($registrationSteps);

        return new ProcessorResult(
            success: true,
            messageKey: 'message.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
