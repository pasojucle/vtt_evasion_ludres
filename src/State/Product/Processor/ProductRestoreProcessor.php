<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\UseCase\v2\Product\RestoreProduct;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class ProductRestoreProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private RestoreProduct $restoreProduct,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $product = $payload->data;
        
        ($this->restoreProduct)($product);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.restore',
            flashType: 'success',
            data: $product,
        );
    }
}
