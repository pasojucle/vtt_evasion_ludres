<?php

declare(strict_types=1);

namespace App\State\Coverage\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Licence;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class CoverageValidateProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var Licence $entity */
        $entity->setCurrentSeasonForm(true);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'coverage.flash.success.valided',
            flashType: 'succes',
        );
    }
}
