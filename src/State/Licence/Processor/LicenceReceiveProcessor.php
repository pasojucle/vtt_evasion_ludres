<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Licence;
use App\Service\FilterDecoderService;
use App\Service\LicenceService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceReceiveProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
        private FilterDecoderService $filterDecoder,
    ) {
    }

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        assert($entity instanceof Licence);
        
        $tansition = ($entity->getState()->isYearly()) ? 'receive_yearly_file' : 'receive_trial_file';
        if ($this->licenceService->applyTransition($entity, $tansition)) {
            $this->entityManager->flush();
            return new ProcessorResult(
                success: true,
                targetRoute: 'admin_registration_list',
                routeParams: $this->filterDecoder->decode($filter),
                messageKey: 'registration.flash.success.received',
                flashType: 'success',
            );
        }

        return new ProcessorResult(
            success: false,
            targetRoute: 'admin_registration_list',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: 'registration.flash.error.received',
            flashType: 'danger',
        );
    }
}
