<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Licence;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @implements HtmlProcessorInterface<Licence>
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        foreach ($entity->getLicenceAgreements() as $licenceAgreement) {
            $this->entityManager->remove($licenceAgreement);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'licence.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
