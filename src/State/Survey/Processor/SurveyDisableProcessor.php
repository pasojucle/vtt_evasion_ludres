<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Survey;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDisableProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var Survey $entity */
        $entity->setDisabled(true);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.success.received',
        );
    }
}
