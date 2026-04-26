<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Filter\OrderFilter;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Mapper\DropdownMapper;
use App\Mapper\PaginatorMapper;
use App\Service\OrderService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderAdminListMapper
{
    public function __construct(
        private DropdownMapper $dropdownMapper,
        private UrlGeneratorInterface $urlGenerator,
        private OrderService $orderService,
        private TranslatorInterface $translator,
        private PaginatorMapper $paginatorMapper,
    ) 
    {

    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, OrderFilter $filter): ListDto
    {
        $listDto = new ListDto();
        $listDto->settings = $this->dropdownMapper->settingsFromSection('ORDER');
        $listDto->tools = $this->getTools();
        $listDto->paginator = $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter);
        foreach ($entities as $entity) {
            $listItem = new ListItemDto(
                $this->getDropdown($entity),
                null,
                $this->urlGenerator->generate("admin_order", ['orderHeader' => $entity->getId()]),
                $this->getAction($entity, $currentPage, $filter),
            );
            $status = $entity->getStatus();
            $listItem
                ->addText($entity->getCreatedAt()->format('d/m/Y'))
                ->addText($entity->getMember()->getIdentity()->getFullName())
                ->addCurrency($this->orderService->getAmount($entity->getOrderLines()))
                ->addBadge($status->trans($this->translator), $status->variant());

            $listDto->addItem($listItem);
        }

        return $listDto;
    }

    private function getAction(OrderHeader $entity, ?int $currentPage, OrderFilter $filter): ?ButtonDto
    {
        $status = $entity->getStatus();
        if ($status === OrderStatusEnum::ORDERED) {
            return new ButtonDto(
                'Valider',
                $this->urlGenerator->generate('admin_order', ['orderHeader'=> $entity->getId()]),
                ButtonDto::TOP,
                'lucide:check-check',
                ColorVariant::SUCCESS,
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
                'Cloturer',
                $this->urlGenerator->generate('admin_order_status', $params),
                'order-list',
                'lucide:check-check',
                ColorVariant::ACCENT,
            );

            $action->addHtmlAttribut('data-turbo-method', 'post');

            return $action;
        }

        return null;
    }

    private function getDropdown(OrderHeader $order): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->addMenuItem(
            'Supprimer',
            $this->urlGenerator->generate('order_delete', ['orderHeader' => $order->getId()]),
            'lucide:delete',
            ButtonDto::MODAL_CONTENT,
        );
        
        return $dropdown;
    }

    private function getTools(): DropdownDto
    {
        $dropdown = new DropdownDto();
        
        $dropdown->position = 'relative';
        $dropdown->addMenuItem(
            'Exporter la sélection',
            $this->urlGenerator->generate('admin_order_headers_export'),
            'lucide:file-down',
        );

        return $dropdown;

    }
}