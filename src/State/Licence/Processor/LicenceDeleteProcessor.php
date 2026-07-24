<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Licence;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<Licence>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        foreach ($entity->getLicenceAgreements() as $licenceAgreement) {
            $this->entityManager->remove($licenceAgreement);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'licence.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
