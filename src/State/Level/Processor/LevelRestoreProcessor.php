<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Level;
use App\Service\SoftDeleteService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LevelRestoreProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param Level $entity
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();;

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'level.flash.success.restore',
        );
    }
}
