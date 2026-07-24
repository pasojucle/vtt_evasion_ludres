<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Level;
use App\Repository\LevelRepository;
use App\Service\OrderByService;
use App\Service\SoftDeleteService;
use App\State\Interface\FormRedirectProcessorInterface;

class LevelDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private LevelRepository $levelRepository,
        private OrderByService $orderByService,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param Level $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $type = $entity->getType();
        $levels = $this->levelRepository->findByType($type);

        $this->softDeleteService->softDelete($entity);
        $this->orderByService->setNewOrders($entity, $levels, count($levels));

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'level.flash.success.delete',
        );
    }
}
