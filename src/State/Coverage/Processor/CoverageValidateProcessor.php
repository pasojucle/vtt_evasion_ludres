<?php

declare(strict_types=1);

namespace App\State\Coverage\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Licence;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class CoverageValidateProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var Licence $entity */
        $entity->setCurrentSeasonForm(true);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'coverage.flash.success.valided',
            flashType: 'succes',
        );
    }
}
