<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\SoftDeleteService;
use App\Service\UrlContextService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class ProductDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $this->softDeleteService->softDelete($payload->data);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
