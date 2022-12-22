<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\OrderHeader;
use App\Form\Admin\OrderFilterType;
use App\Repository\OrderHeaderRepository;
use App\Service\ExportService;
use App\Service\PaginatorService;
use App\ViewModel\OrderPresenter;
use App\ViewModel\OrdersPresenter;
use App\ViewModel\Paginator\PaginatorPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        PaginatorPresenter $paginatorPresenter,
        Request $request,
        bool $filtered
    ): Response {
        $filters = ($filtered) ? $request->getSession()->get('admin_orders_filters') ?? [] : [];
        $filters['p'] = $request->query->get('p');

        $form = $this->createForm(OrderFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $request->query->set('p', 1);

            return $this->redirectToRoute('admin_orders', [
                'filtered' => true,
            ]);
        }
        $request->getSession()->set('admin_orders_filters', $filters);
        $request->getSession()->set('order_return', $this->generateUrl('admin_orders', [
            'filtered' => (int) $filtered,
        ]));

        $query = $this->orderHeaderRepository->findOrdersQuery($filters);
        $orders = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($orders);
        $paginatorPresenter->present($orders, array_merge($filters, ['filtered' => (int) $filtered]));

        return $this->render('order/admin/list.html.twig', [
            'form' => $form->createView(),
            'orders' => $presenter->viewModel()->orders,
            'paginator' => $paginatorPresenter->viewModel(),
        ]);
    }

    #[Route('/admin/command/status/{orderHeader}/{status}', name: 'admin_order_status', methods: ['GET'])]
    public function adminOrderValidate(
        OrdersPresenter $presenter,
        PaginatorService $paginator,
        PaginatorPresenter $paginatorPresenter,
        Request $request,
        OrderHeader $orderHeader,
        int $status
    ): Response {
        $filters = $request->getSession()->get('admin_orders_filters') ?? [];
        $request->query->set('p', $filters['p']);
        $orderHeader->setStatus($status);
        $this->entityManager->flush();
        $query = $this->orderHeaderRepository->findOrdersQuery($filters);
        $orders = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($orders);
        $paginatorPresenter->present($orders, array_merge($filters, ['filtered' => true]), 'admin_orders');

        return $this->render('order/admin/list.html.twig', [
            'orders' => $presenter->viewModel()->orders,
            'paginator' => $paginatorPresenter->viewModel()
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
    ): Response {
        $filters = $request->getSession()->get('admin_orders_filters');

        $orderHeaders = $this->orderHeaderRepository->findOrdersQuery($filters)->getQuery()->getResult();
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
