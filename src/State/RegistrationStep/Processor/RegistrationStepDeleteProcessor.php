<?php

declare(strict_types=1);

namespace App\State\RegistrationStep\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\RegistrationStep;
use App\Repository\RegistrationStepRepository;
use App\Service\OrderByService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationStepDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RegistrationStepRepository $registrationStepRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var RegistrationStep $entity */
        $group = $entity->getRegistrationStepGroup();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $registrationSteps = $this->registrationStepRepository->findByGroup($group->getId());
        $this->orderByService->resetOrders($registrationSteps);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'message.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
