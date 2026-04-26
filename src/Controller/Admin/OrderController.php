<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\OrderDtoTransformer;
use App\Dto\Filter\OrderFilter;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Form\Admin\OrderFilterType;
use App\Form\Admin\OrderType as AdminOrderType;
use App\Repository\OrderHeaderRepository;
use App\Service\ExportService;
use App\Service\FilterDecoderService;
use App\State\Order\Provider\OrderAdminListProvider;
use App\UseCase\Order\SetOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    ) {
    }

    #[Route('/admin/commandes', name: 'admin_orders', methods: ['GET'])]
    #[IsGranted('PRODUCT_LIST')]
    public function adminOrders(
        Request $request,
        OrderAdminListProvider $provider,
    ): Response {
        $statusQuery = $request->query->get('status');
        $filter = new OrderFilter($statusQuery ? OrderStatusEnum::tryFrom($statusQuery) : $statusQuery);

        $form = $this->createForm(OrderFilterType::class, $filter);
        $form->handleRequest($request);

        return $this->render('order/admin/list.html.twig', [
            'form' => $form->createView(),
            'list' => $provider->getCollection(
                $filter, 
                $request->attributes->get('_route'), 
                $request->query->getInt('page', 1),
            ),
        ]);
    }

    #[Route('/admin/command/status/{orderHeader}/{status}', name: 'admin_order_status', methods: ['POST'], requirements:['status' => OrderStatusEnum::VALIDED->value . '|' . OrderStatusEnum::COMPLETED->value])]
    #[IsGranted('PRODUCT_EDIT', 'orderHeader')]
    public function adminOrderValidate(
        Request $request,
        OrderHeader $orderHeader,
        FilterDecoderService $filterDecoder,
        OrderStatusEnum $status
    ): Response {


        $orderHeader->setStatus($status);
        $this->entityManager->flush();
        $queries = $filterDecoder->decode($request->query->get('filter'));
        
        return $this->redirectToRoute('admin_orders', $queries, Response::HTTP_SEE_OTHER);
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
