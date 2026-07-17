<?php

declare(strict_types=1);

namespace App\State\BoardRole\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\BoardRole;
use App\Service\SoftDeleteService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BoardRoleRestoreProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param BoardRole $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();
        ;

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'board_role.flash.success.restore',
        );
    }
}
