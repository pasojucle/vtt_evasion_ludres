<?php

declare(strict_types=1);

namespace App\State\RegistrationStep\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\RegistrationStep;
use App\Repository\RegistrationStepRepository;
use App\Service\OrderByService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationStepDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RegistrationStepRepository $registrationStepRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var RegistrationStep $entity */
        $group = $entity->getRegistrationStepGroup();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $registrationSteps = $this->registrationStepRepository->findByGroup($group->getId());
        $this->orderByService->resetOrders($registrationSteps);

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'message.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
