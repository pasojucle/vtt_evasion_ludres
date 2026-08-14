<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\OrderFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\WikiMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\OrderService;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderAdminListMapper
{
    public function __construct(
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private UrlGeneratorInterface $urlGenerator,
        private OrderService $orderService,
        private TranslatorInterface $translator,
        private PaginatorMapper $paginatorMapper,
        private FilterChipsMapper $filterChipsMapper,
        private WikiMapper $wikiMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, OrderFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));
        $items = [];
        /** @var OrderHeader $entity */
        foreach ($entities as $entity) {
            $status = $entity->getStatus();
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getCreatedAt()->format('d/m/y')),
                    new LabelView($entity->getMember()->getIdentity()->getFullName()),
                    new LabelView($this->orderService->getAmount($entity->getOrderLines(), $entity->getMember()), LabelView::TYPE_NUMBER),
                ],
                status: new BadgeView($status->trans($this->translator), $status->variant()),
                dropdown: $this->getDropdown($entity, $referer),
                url: $this->urlGenerator->generate("admin_order", ['orderHeader' => $entity->getId()]),
                action: $this->getAction($entity, $currentPage, $filter),
                gridTemplateRow: 'grid-cols-1 lg:grid-cols-[1fr_112px]',
                gridTemplateContent: 'grid-cols-1 lg:grid-cols-[1fr_112px]',
                gridTemplateLabels: 'grid-cols-[80px_auto_80px] lg:grid-cols-3',
                gridTemplateBadges: 'grid-cols-1',
            );
        }

        return new ListView(
            name: 'orders',
            title: 'Commandes',
            description: 'Administration des commandes de la boutique: état des stocks, validation.',
            items: $items,
            settings: $this->dropdownSettingsMapper->mapToView('ORDER', $referer, RoundedVariant::ROUNDED_END),
            tools: $this->getTools($filter->toArray()),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            wiki:  $this->wikiMapper->mapToView('boutique', RoundedVariant::ROUNDED_START),
        );
    }

    private function getAction(OrderHeader $entity, ?int $currentPage, OrderFilter $filter): ?LinkView
    {
        $status = $entity->getStatus();
        if ($status === OrderStatusEnum::ORDERED) {
            return new LinkView(
                label: 'Valider',
                url: $this->urlGenerator->generate('admin_order', ['orderHeader' => $entity->getId()]),
                icon: 'lucide:check-check',
                variant: ColorVariant::SUCCESS,
                size: Size::SM,
            );
        }
        if ($status === OrderStatusEnum::VALIDED) {
            $params = [
                'orderHeader' => $entity->getId(),
                'status' => OrderStatusEnum::COMPLETED->value,
            ];
            if ($filterHash = $filter->toEncodedString($currentPage)) {
                $params['filter'] = $filterHash;
            }
            $action = new LinkView(
                label: 'Cloturer',
                url: $this->urlGenerator->generate('admin_order_status', $params),
                icon: 'lucide:check-check',
                variant: ColorVariant::ACCENT,
                size: Size::SM,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', 'order-list'),
                    new HtmlAttributView('data-turbo-method', 'post'),
                ]
            );

            return $action;
        }

        return null;
    }

    private function getDropdown(OrderHeader $order, string $referer): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Supprimer',
                    url: $this->urlContextService->generateUrl('order_delete', ['orderHeader' => $order->getId()], $referer),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                )
            ]
        );
    }

    private function getTools(array $filter): DropdownView
    {
        $dropdown = new DropdownView(
            variant: DropdownVariant::GOST,
            menuItems: [
                new LinkView(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_order_headers_export', $filter),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                        new HtmlAttributView('data-turbo', 'false')
                    ]
                ),
            ],
        );

        return $dropdown;
    }
}
