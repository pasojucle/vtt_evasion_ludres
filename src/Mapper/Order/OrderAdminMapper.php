<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemUrlView;
use App\Dto\View\ListItemView;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Service\OrderService;
use App\Service\UrlContextService;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderAdminMapper
{
    public function __construct(
        private OrderService $orderService,
        private TranslatorInterface $translator,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(OrderHeader $entity, string $targetUrl): ListItemView
    {
        $status = $entity->getStatus();

        return new ListItemView(
                id: sprintf('order-item-%s', $entity->getId()),
                labels: [
                    new LabelView($entity->getCreatedAt()->format('d/m/y')),
                    new LabelView($entity->getMember()->getIdentity()->getFullName()),
                    new LabelView($this->orderService->getAmount($entity->getOrderLines(), $entity->getMember()), LabelView::TYPE_NUMBER),
                ],
                status: new BadgeView($status->trans($this->translator), $status->variant()),
                dropdown: $this->getDropdown($entity, $targetUrl),
                url: new ListItemUrlView(
                    $this->urlContextService->generateUrl("admin_order", ['orderHeader' => $entity->getId()], $targetUrl),
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    ]
                ),
                action: $this->getAction($entity, $targetUrl),
                gridTemplateRow: 'grid-cols-1 lg:grid-cols-[1fr_112px]',
                gridTemplateContent: 'grid-cols-1 lg:grid-cols-[1fr_112px]',
                gridTemplateLabels: 'grid-cols-[80px_auto_80px] lg:grid-cols-3',
                gridTemplateBadges: 'grid-cols-1',
            );
    }

    private function getAction(OrderHeader $entity, string $targetUrl): ?LinkView
    {
        $status = $entity->getStatus();
        if ($status === OrderStatusEnum::ORDERED) {
            return new LinkView(
                label: 'Valider',
                url: $this->urlContextService->generateUrl('admin_order', ['orderHeader' => $entity->getId()], $targetUrl),
                icon: 'lucide:check-check',
                variant: ColorVariant::SUCCESS,
                size: Size::SM,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            );
        }
        if ($status === OrderStatusEnum::VALIDED) {
            $action = new LinkView(
                label: 'Cloturer',
                url: $this->urlContextService->generateUrl('admin_order_status', [
                    'orderHeader' => $entity->getId(),
                ], $targetUrl),
                icon: 'lucide:check-check',
                variant: ColorVariant::ACCENT,
                size: Size::SM,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', 'order-list'),
                    new HtmlAttributeView('data-turbo-method', 'post'),
                ]
            );

            return $action;
        }

        return null;
    }

    private function getDropdown(OrderHeader $order, string $targetUrl): ?DropdownView
    {
        if (in_array($order->getStatus(), [OrderStatusEnum::CANCELED, OrderStatusEnum::COMPLETED], true)) {
            return null;
        }

        return new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Supprimer',
                    url: $this->urlContextService->generateUrl('order_delete', ['orderHeader' => $order->getId()], $targetUrl),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                )
            ]
        );
    }
}
