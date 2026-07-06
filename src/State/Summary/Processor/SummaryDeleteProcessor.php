<?php

declare(strict_types=1);

namespace App\State\Summary\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Summary;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SummaryDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }
    /**
     * @implements HtmlProcessorInterface<Summary>
     */

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'slideshow.image.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
