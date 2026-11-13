<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Core\Dto\HandlerContext;
use App\Dto\View\EmailView;
use App\Dto\View\Order\OrderSheetView;
use App\Dto\View\PhoneView;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use App\Service\UrlContextService;

class OrderValidateMapper
{
    public function __construct(
        private OrderLineMapper $orderLineMapper,
        private UrlContextService $urlContextService,
    ){}

    public function mapToView(OrderHeader $order, HandlerContext $context): OrderSheetView
    {
        $target = $this->urlContextService->decodeUrl($context->encodedFallback);
        $identity = $order->getMember()->getMainIdentity();

        return new OrderSheetView(
            title: 'Valider une commande',
            description: 'Valider une commande en définissant la disponiblité de chaque articles...',
            action: 'Valider',
            frame: sprintf('order-update-%s', $order->getId() ?? ''),
            fullName: $identity->getFullName(),
            phone: new PhoneView($identity->getMobile()),
            email: new EmailView($identity->getEmail()),
            orderLines: $order->getOrderLines()->map(
                fn(OrderLine $line) => $this->orderLineMapper->mapToview(
                    $line, 
                    $order->getMember(),
                    OrderStatusEnum::ORDERED === $order->getStatus(),
                    $target
                )
            )->toArray(),
            amount: number_format($order->getAmount(), 2) . ' €',
            comments: $order->getComments(),
        );
    }
}