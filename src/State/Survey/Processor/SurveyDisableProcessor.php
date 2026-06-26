<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Survey;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDisableProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var Survey $entity */
        $entity->setDisabled(true);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.success.received',
        );
    }
}
