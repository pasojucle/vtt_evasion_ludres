<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Licence;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @implements DialogProcessorInterface<Licence>
     */
    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        foreach ($entity->getLicenceAgreements() as $licenceAgreement) {
            $this->entityManager->remove($licenceAgreement);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            messageKey: 'licence.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
