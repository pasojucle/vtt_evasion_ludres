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

class OrderValidateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateOrder $updateOrder,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $orderHeader = $payload->data;
        $clickedButton = $payload->clickedButton;

        $status = match($clickedButton?->getName()) {
            'validate' => OrderStatusEnum::VALIDED,
            'complete' => OrderStatusEnum::COMPLETED,
            'cancel' => OrderStatusEnum::CANCELED,
            'unValide' => OrderStatusEnum::ORDERED,
            default => null,
        };

        ($this->updateOrder)($orderHeader, $status);

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'order.flash.success.validate',
        );
    }
}
