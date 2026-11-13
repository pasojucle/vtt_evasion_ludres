<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ListExportHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Filter\OrderFilter;
use App\Entity\OrderHeader;
use App\Form\Admin\OrderType as AdminOrderType;
use App\State\Order\Processor\OrderCompleteProcessor;
use App\State\Order\Processor\OrderValidateProcessor;
use App\State\Order\Provider\OrderAdminListExportProvider;
use App\State\Order\Provider\OrderAdminListProvider;
use App\State\Order\Provider\OrderCompleteProvider;
use App\State\Order\Provider\OrderValidateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractController
{
    #[Route('/admin/commandes', name: 'admin_order_list', methods: ['GET'])]
    #[IsGranted('PRODUCT_LIST')]
    public function adminOrders(
        Request $request,
        OrderAdminListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/admin/command/status/{orderHeader}/completed', 
        name: 'admin_order_status', 
        methods: ['POST'], 
        requirements:['status' => '\d+'])
    ]
    #[IsGranted('PRODUCT_EDIT', 'orderHeader')]
    public function adminOrderValidate(
        Request $request,
        OrderCompleteProvider $provider,
        OrderCompleteProcessor $processor,
        OrderHeader $orderHeader,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $orderHeader,
            $provider,
            $processor,
        );
    }

    #[Route('/admin/commande/{orderHeader}', name: 'admin_order', requirements:['orderHeader' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_EDIT', 'orderHeader')]
    public function admin_order(
        Request $request,
        OrderValidateProvider $provider,
        OrderValidateProcessor $processor,
        OrderHeader $orderHeader,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $orderHeader,
            $provider,
            $processor,
            AdminOrderType::class,
        );
    }

    #[Route('/admin/export/commande', name: 'admin_order_headers_export', methods: ['GET'])]
    #[IsGranted('PRODUCT_LIST')]
    public function adminOrderHeadersExport(
        OrderAdminListExportProvider $provider,
        Request $request,
        ListExportHandler $handler,
    ): StreamedResponse {
        return $handler->handle(
            $request,
            OrderFilter::class,
            $provider,
        );
    }
}
