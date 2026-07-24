<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Licence;
use App\Service\LicenceService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceReceiveProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var Licence $entity */
        $tansition = ($entity->getState()->isYearly()) ? 'receive_yearly_file' : 'receive_trial_file';
        if ($this->licenceService->applyTransition($entity, $tansition)) {
            $this->entityManager->flush();
            return new RedirectProcessorResult(
                success: true,
                targetUrl: $targetUrl,
                messageKey: 'registration.flash.success.received',
                flashType: 'success',
            );
        }

        return new RedirectProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.error.received',
            flashType: 'danger',
        );
    }
}
