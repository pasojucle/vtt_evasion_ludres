<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\OrderHeader;
use App\Service\ExportService;
use App\Service\PaginatorService;
use App\ViewModel\OrderPresenter;
use App\ViewModel\OrdersPresenter;
use App\Form\Admin\OrderFilterType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderHeaderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    public function __construct(
        private OrderPresenter $presenter,
        private OrderHeaderRepository $orderHeaderRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/admin/commandes/{filtered}', name: 'admin_orders', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    public function adminOrders(
        OrdersPresenter $presenter,
        PaginatorService $paginator,
        Request $request,
        bool $filtered
    ): Response {
        $filters = ($filtered) ? $request->getSession()->get('admin_orders_filters') : [];

        $form = $this->createForm(OrderFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $request->getSession()->set('admin_orders_filters', $filters);
            $request->query->set('p', 1);

            return $this->redirectToRoute('admin_orders', [
                'filtered' => true,
            ]);
        }
        $request->getSession()->set('order_return', $this->generateUrl('admin_orders', [
            'filtered' => (int) $filtered,
        ]));

        $query = $this->orderHeaderRepository->findOrdersQuery($filters);
        $orders = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($orders);

        return $this->render('order/admin/list.html.twig', [
            'form' => $form->createView(),
            'orders' => $presenter->viewModel()->orders,
            'lastPage' => $paginator->lastPage($orders),
            'current_filters' => $filters,
            'count' => $paginator->total($orders),
        ]);
    }

    #[Route('/admin/command/status/{orderHeader}/{status}', name: 'admin_order_status', methods: ['GET'])]
    public function adminOrderValidate(
        OrdersPresenter $presenter,
        PaginatorService $paginator,
        Request $request,
        OrderHeader $orderHeader,
        int $status
    ): Response {
        $filters = $request->getSession()->get('admin_orders_filters');
        $orderHeader->setStatus($status);
        $this->entityManager->flush();
        $query = $this->orderHeaderRepository->findOrdersQuery($filters);
        $orders = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($orders);

        return $this->render('order/admin/list.html.twig', [
            'orders' => $presenter->viewModel()->orders,
            'lastPage' => $paginator->lastPage($orders),
            'count' => $paginator->total($orders),
            'target_route' => 'admin_orders',
            'current_Filters' => [
                'filterd' => true,
            ],
        ]);
    }

    #[Route('/admin/commande/{orderHeader}', name: 'admin_order', methods: ['GET'])]
    public function admin_order(
        Request $request,
        ?OrderHeader $orderHeader
    ): Response {
        $this->presenter->present($orderHeader);
        $request->getSession()->set('user_return', $this->generateUrl('admin_order', [
            'orderHeader' => $orderHeader->getId(),
        ]));

        return $this->render('order/admin/show.html.twig', [
            'order' => $this->presenter->viewModel(),
        ]);
    }


    #[Route('/admin/export/commande', name: 'admin_order_headers_export', methods: ['GET'])]
    public function adminOrderHeadersExport(
        ExportService $exportService,
        OrdersPresenter $presenter,
        Request $request,
        PaginatorService $paginator
    ): Response
    {
        $filters = $request->getSession()->get('admin_orders_filters');

        $query = $this->orderHeaderRepository->findOrdersQuery($filters);
        $orderHeaders = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($orderHeaders);
        $content = $exportService->exportOrderHeaders($presenter->viewModel()->orders);

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_commandes.csv'
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
