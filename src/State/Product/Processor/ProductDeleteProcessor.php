<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Product;
use App\Service\UrlContextService;
use App\UseCase\v2\Product\DeleteProduct;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class ProductDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private DeleteProduct $deleteProduct,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /**
         * @var Product $data
         */
        $data = $payload->data;
        ($this->deleteProduct)($data);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
