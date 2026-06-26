<?php

declare(strict_types=1);

namespace App\State\Coverage\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Licence;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class CoverageValidateProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var Licence $entity */
        $entity->setCurrentSeasonForm(true);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'coverage.flash.success.valided',
            flashType: 'succes',
        );
    }
}
