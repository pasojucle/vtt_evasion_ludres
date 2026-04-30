<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Filter\OrderFilter;
use App\Dto\HtmlAttributDto;
use App\Dto\ListCellItemDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Mapper\DropdownMapper;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\OrderService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderAdminListMapper
{
    public function __construct(
        private DropdownMapper $dropdownMapper,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private UrlGeneratorInterface $urlGenerator,
        private OrderService $orderService,
        private TranslatorInterface $translator,
        private PaginatorMapper $paginatorMapper,
    ) 
    {

    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, OrderFilter $filter): ListDto
    {
        $items = [];
        foreach ($entities as $entity) {
            $status = $entity->getStatus();
            $items[] = new ListItemDto(
                cells: [
                    new ListCellItemDto($entity->getCreatedAt()->format('d/m/Y')),
                    new ListCellItemDto($entity->getMember()->getIdentity()->getFullName()),
                    new ListCellItemDto($this->orderService->getAmount($entity->getOrderLines()), ListCellItemDto::TYPE_NUMBER),
                    new ListCellItemDto($status->trans($this->translator), ListCellItemDto::TYPE_BADGE, $status->variant()),
                ],
                dropdown: $this->getDropdown($entity),
                url: $this->urlGenerator->generate("admin_order", ['orderHeader' => $entity->getId()]),
                action: $this->getAction($entity, $currentPage, $filter),
            );
        }

        return new ListDto(
            items: $items,
            settings: $this->dropdownSettingsMapper->mapToView('ORDER'),
            tools: $this->getTools(),
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
        );
    }

    private function getAction(OrderHeader $entity, ?int $currentPage, OrderFilter $filter): ?ButtonDto
    {
        $status = $entity->getStatus();
        if ($status === OrderStatusEnum::ORDERED) {
            return new ButtonDto(
                label: 'Valider',
                url: $this->urlGenerator->generate('admin_order', ['orderHeader'=> $entity->getId()]),
                icon: 'lucide:check-check',
                variant: ColorVariant::SUCCESS,
            );
        }
        if ($status === OrderStatusEnum::VALIDED) {
            $params = [
                'orderHeader'=> $entity->getId(),
                'status' => OrderStatusEnum::COMPLETED->value,
            ];
            if ($filterHash = $filter->toEncodedString($currentPage)) {
                $params['filter'] = $filterHash;
            }
            $action =  new ButtonDto(
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
                    htmlAttributes: [
                        new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                    ],
                )
            ]
        );
    }

    private function getTools(): DropdownDto
    {
        $dropdown = new DropdownDto(
            position: 'relative',
            menuItems: [
                new ButtonDto(
                    'Exporter la sélection',
                    $this->urlGenerator->generate('admin_order_headers_export'),
                    'lucide:file-down',
                )
            ]
        );

        return $dropdown;
    }
}