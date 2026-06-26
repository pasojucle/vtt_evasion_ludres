<?php

declare(strict_types=1);

namespace App\State\Summary\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Summary;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SummaryDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }
    /**
     * @implements DialogProcessorInterface<Summary>
     */

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            messageKey: 'slideshow.image.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
