<?php

declare(strict_types=1);

namespace App\State\Order\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Enum\OrderStatusEnum;
use App\Service\UrlContextService;
use App\UseCase\v2\Order\UpdateOrder;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class OrderDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private UpdateOrder $updateOrder,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $order = $payload->data;

        ($this->updateOrder)($order, OrderStatusEnum::CANCELED);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'order.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
