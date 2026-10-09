<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Product\UpdateProduct;

class ProductUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateProduct $updateProduct,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $product = $payload->data;
        $id = $product->getId();
        
        ($this->updateProduct)($product, $payload->files);

        $messageKey = 'activity.flash.success.edit';
        if (!$id) {
            $messageKey = 'product.flash.success.create';
        }
        
        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
