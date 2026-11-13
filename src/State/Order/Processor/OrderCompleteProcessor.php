<?php

declare(strict_types=1);

namespace App\State\Order\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\Enum\OrderStatusEnum;
use App\Service\CsrfTokenService;
use App\UseCase\v2\Order\UpdateOrder;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class OrderCompleteProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private UpdateOrder $updateOrder,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $order = $payload->data;
        $tokenId = $this->csrfTokenService->getTokenId($order);
        $csrfToken = new CsrfToken($tokenId, $payload->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            new TurboStreamProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
            );
        }

        ($this->updateOrder)($order, OrderStatusEnum::COMPLETED);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'order.flash.success.complete',
            flashType: 'success',
            data: $order
        );
    }
}
