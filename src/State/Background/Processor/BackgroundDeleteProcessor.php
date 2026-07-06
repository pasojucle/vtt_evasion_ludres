<?php

declare(strict_types=1);

namespace App\State\Background\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Background;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BackgroundDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var Background $entity */
        $this->entityManager->remove($entity);

        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'background.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
