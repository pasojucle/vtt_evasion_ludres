<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Licence;
use App\Service\LicenceService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceReceiveProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var Licence $entity */
        $tansition = ($entity->getState()->isYearly()) ? 'receive_yearly_file' : 'receive_trial_file';
        if ($this->licenceService->applyTransition($entity, $tansition)) {
            $this->entityManager->flush();
            return new ProcessorResult(
                success: true,
                targetUrl: $targetUrl,
                messageKey: 'registration.flash.success.received',
                flashType: 'success',
            );
        }

        return new ProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.error.received',
            flashType: 'danger',
        );
    }
}
