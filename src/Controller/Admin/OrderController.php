<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\OrderDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Form\Admin\OrderFilterType;
use App\Form\Admin\OrderType as AdminOrderType;
use App\Form\OrderType;
use App\Repository\OrderHeaderRepository;
use App\Repository\ParameterRepository;
use App\Service\ExportService;
use App\Service\MessageService;
use App\Service\PaginatorService;
use App\UseCase\Order\SetOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractController
{
    public function __construct(
        private OrderDtoTransformer $orderDtoTransformer,
        private OrderHeaderRepository $orderHeaderRepository,
        private EntityManagerInterface $entityManager,
        private PaginatorService $paginator,
        private PaginatorDtoTransformer $paginatorDtoTransformer,
    ) {
    }

    #[Route('/admin/commandes/{filtered}', name: 'admin_orders', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    #[IsGranted('PRODUCT_LIST')]
    public function adminOrders(
        ParameterRepository $parameterRepository,
        MessageService $messageService,
        Request $request,
        bool $filtered
    ): Response {
        $filters = ($filtered) ? $request->getSession()->get('admin_orders_filters') ?? [] : [];
        $filters['p'] = $request->query->get('p') ?? 1;

        $form = $this->createForm(OrderFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $request->query->set('p', 1);
        }
        $request->getSession()->set('admin_orders_filters', $filters);
        $request->getSession()->set('order_return', $this->generateUrl('admin_orders', [
            'filtered' => (int) $filtered,
        ]));

        $query = $this->orderHeaderRepository->findOrdersQuery($filters);
        $orders = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('order/admin/list.html.twig', [
            'form' => $form->createView(),
            'orders' => $this->orderDtoTransformer->fromEntities($orders),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($orders, array_merge($filters, ['filtered' => (int) $filtered])),
            'settings' => [
                'parameters' => $parameterRepository->findByParameterGroupName('ORDER'),
                'messages' => $messageService->getMessagesBySectionName('ORDER'),
            ],
        ]);
    }

    #[Route('/admin/command/status/{orderHeader}/{status}', name: 'admin_order_status', methods: ['GET'], requirements:['status' => OrderStatusEnum::VALIDED->value . '|' . OrderStatusEnum::COMPLETED->value])]
    #[IsGranted('PRODUCT_EDIT', 'orderHeader')]
    public function adminOrderValidate(
        Request $request,
        OrderHeader $orderHeader,
        OrderStatusEnum $status
    ): Response {
        $filters = $request->getSession()->get('admin_orders_filters') ?? [];
        $request->query->set('p', $filters['p']);
        $orderHeader->setStatus($status);
        $this->entityManager->flush();
        $query = $this->orderHeaderRepository->findOrdersQuery($filters);
        $orders = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('order/admin/list.html.twig', [
            'orders' => $this->orderDtoTransformer->fromEntities($orders),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($orders, array_merge($filters, ['filtered' => true]), 'admin_orders'),
        ]);
    }

    #[Route('/admin/commande/{orderHeader}', name: 'admin_order', methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_EDIT', 'orderHeader')]
    public function admin_order(
        Request $request,
        SetOrder $setOrder,
        ?OrderHeader $orderHeader
    ): Response {
        $request->getSession()->set('user_return', $this->generateUrl('admin_order', [
            'orderHeader' => $orderHeader->getId(),
        ]));
        $form = $this->createForm(AdminOrderType::class, $orderHeader, [
            'status' => $orderHeader->getStatus(),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            list($route, $params) = $setOrder->execute($form, $orderHeader);
            return $this->redirectToRoute($route, $params);
        }

        return $this->render('order/admin/show.html.twig', [
            'order' => $this->orderDtoTransformer->fromEntity($orderHeader),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/export/commande', name: 'admin_order_headers_export', methods: ['GET'])]
    #[IsGranted('PRODUCT_LIST')]
    public function adminOrderHeadersExport(
        ExportService $exportService,
        Request $request,
    ): Response {
        $filters = $request->getSession()->get('admin_orders_filters');

        $orderHeaders = $this->orderHeaderRepository->findOrdersQuery($filters)->getQuery()->getResult();
        $content = $exportService->exportOrderHeaders($this->orderDtoTransformer->fromEntities($orderHeaders));

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_commandes.csv'
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
