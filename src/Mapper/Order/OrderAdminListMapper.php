<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Dto\BadgeDto;
use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Filter\OrderFilter;
use App\Dto\HtmlAttributDto;
use App\Dto\LabelDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\OrderService;
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
    ) {
    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, OrderFilter $filter, FilterConfigInterface $filterConfig): ListDto
    {
        $items = [];
        /** @var OrderHeader $entity */
        foreach ($entities as $entity) {
            $status = $entity->getStatus();
            $items[] = new ListItemDto(
                labels: [
                    new LabelDto($entity->getCreatedAt()->format('d/m/y')),
                    new LabelDto($entity->getMember()->getIdentity()->getFullName()),
                    new LabelDto($this->orderService->getAmount($entity->getOrderLines(), $entity->getMember()), LabelDto::TYPE_NUMBER),
                ],
                status: new BadgeDto($status->trans($this->translator), $status->variant()),
                dropdown: $this->getDropdown($entity),
                url: $this->urlGenerator->generate("admin_order", ['orderHeader' => $entity->getId()]),
                action: $this->getAction($entity, $currentPage, $filter),
            );
        }

        return new ListDto(
            items: $items,
            settings: $this->dropdownSettingsMapper->mapToView('ORDER', RoundedVariant::ROUNDED_END),
            tools: $this->getTools($filter->toArray()),
            advancedFilter: new ButtonDto(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_orders'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::SHEET_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            wiki: new ButtonDto(
                url: $this->urlGenerator->generate('wiki_show', ['directory' => 'boutique']),
                title: 'wiki',
                icon: 'lucide:circle-help',
                variant: ColorVariant::DEFAULT,
                rounded: RoundedVariant::ROUNDED_START,
                htmlAttributes: [
                    new HtmlAttributDto('target', '_blank'),
                ],
            ),
        );
    }

    private function getAction(OrderHeader $entity, ?int $currentPage, OrderFilter $filter): ?ButtonDto
    {
        $status = $entity->getStatus();
        if ($status === OrderStatusEnum::ORDERED) {
            return new ButtonDto(
                label: 'Valider',
                url: $this->urlGenerator->generate('admin_order', ['orderHeader' => $entity->getId()]),
                icon: 'lucide:check-check',
                variant: ColorVariant::SUCCESS,
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
            $action = new ButtonDto(
                label: 'Cloturer',
                url: $this->urlGenerator->generate('admin_order_status', $params),
                icon: 'lucide:check-check',
                variant: ColorVariant::ACCENT,
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', 'order-list'),
                    new HtmlAttributDto('data-turbo-method', 'post'),
                ]
            );

            return $action;
        }

        return null;
    }

    private function getDropdown(OrderHeader $order): DropdownDto
    {
        return  new DropdownDto(
            menuItems: [
                new ButtonDto(
                    label: 'Supprimer',
                    url: $this->urlGenerator->generate('order_delete', ['orderHeader' => $order->getId()]),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                    ],
                )
            ]
        );
    }

    private function getTools(array $filter): DropdownDto
    {
        $dropdown = new DropdownDto(
            variant: DropdownVariant::GOST,
            menuItems: [
                new ButtonDto(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_order_headers_export',  $filter),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                ),
            ],
        );

        return $dropdown;
    }
}
