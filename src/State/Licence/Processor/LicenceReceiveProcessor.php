<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Licence;
use App\Service\LicenceService;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceReceiveProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var Licence $entity */
        $tansition = ($entity->getState()->isYearly()) ? 'receive_yearly_file' : 'receive_trial_file';
        if ($this->licenceService->applyTransition($entity, $tansition)) {
            $this->entityManager->flush();
            return new HtmlProcessorResult(
                success: true,
                targetUrl: $targetUrl,
                messageKey: 'registration.flash.success.received',
                flashType: 'success',
            );
        }

        return new HtmlProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.error.received',
            flashType: 'danger',
        );
    }
}
