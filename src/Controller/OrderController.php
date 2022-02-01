<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\OrderHeader;
use App\Form\Admin\OrderFilterType;
use App\Form\OrderType;
use App\Repository\OrderHeaderRepository;
use App\Service\Order\OrderLinesSetService;
use App\Service\Order\OrderValidateService;
use App\Service\PaginatorService;
use App\Service\PdfService;
use App\ViewModel\OrderPresenter;
use App\ViewModel\OrdersPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private OrderPresenter $presenter;

    private OrderHeaderRepository $orderHeaderRepository;

    private EntityManagerInterface $entityManager;

    private RequestStack $requestStack;

    public function __construct(
        OrderPresenter $presenter,
        OrderHeaderRepository $orderHeaderRepository,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->presenter = $presenter;
        $this->orderHeaderRepository = $orderHeaderRepository;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();
    }

    /**
     * @Route("/mon-panier", name="order_edit")
     */
    public function orderEdit(
        OrderLinesSetService $orderLinesSetService,
        OrderValidateService $orderValidateService,
        Request $request
    ): Response {
        $user = $this->getUser();
        if (null === $user) {
            return $this->redirectToRoute('home');
        }
        $orderHeader = $this->orderHeaderRepository->findOneOrderByUser($user);
        if ($request->isXmlHttpRequest()) {
            $orderLinesSetService->execute($request);
        }
        $form = $this->createForm(OrderType::class, $orderHeader);

        if (!$request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $orderValidateService->execute($form);

                return $this->redirectToRoute('order', [
                    'orderHeader' => $orderHeader->getId(),
                ]);
            }
        }

        $this->presenter->present($orderHeader);

        return $this->render('order/edit.html.twig', [
            'order' => $this->presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ma-commande/{orderHeader}", name="order")
     */
    public function order(
        ?OrderHeader $orderHeader
    ): Response {
        $this->presenter->present($orderHeader);

        return $this->render('order/show.html.twig', [
            'order' => $this->presenter->viewModel(),
        ]);
    }

    /**
     * @Route("/confirmation-commande/{orderHeader}", name="order_acknowledgement")
     */
    public function orderAcknowledgement(
        PdfService $pdfService,
        OrderHeader $orderHeader
    ): Response {
        $this->presenter->present($orderHeader);
        $orderAcknowledgement = $this->renderView('order/acknowledgement.html.twig', [
            'order' => $this->presenter->viewModel(),
        ]);
        $pdfFilepath = $pdfService->makePdf($orderAcknowledgement, 'order_acknowledgement_temp', '../data/');

        $fileContent = file_get_contents($pdfFilepath);

        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'commande_vtt_evasion_ludres.pdf'
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    /**
     * @Route("/commande/supprimer/{orderHeader}", name="order_delete")
     */
    public function orderDelete(
        Request $request,
        OrderPresenter $presenter,
        OrderHeader $orderHeader
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'order_delete',
                [
                    'orderHeader' => $orderHeader->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $orderHeader->setStatus(OrderHeader::STATUS_CANCELED);
            $this->entityManager->persist($orderHeader);
            $this->entityManager->flush();

            return $this->redirect($this->requestStack->getSession()->get('order_return'));
        }

        $presenter->present($orderHeader);

        return $this->render('order/delete.modal.html.twig', [
            'order_header' => $presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mes-commandes", name="user_orders")
     */
    public function userOrders(
        OrdersPresenter $presenter,
        PaginatorService $paginator,
        Request $request
    ): Response {
        $user = $this->getUser();
        $query = $this->orderHeaderRepository->findOrdersByUserQuery($user);
        $orders = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($orders);

        $this->requestStack->getSession()->set('order_return', $this->generateUrl('user_orders'));

        return $this->render('order/list.html.twig', [
            'orders' => $presenter->viewModel()->orders,
        ]);
    }

    /**
     * @Route("/admin/commandes/{filtered}", name="admin_orders", defaults={"filtered"=0})
     */
    public function adminOrders(
        OrdersPresenter $presenter,
        PaginatorService $paginator,
        Request $request,
        bool $filtered
    ): Response {
        $filters = ($filtered) ? $this->session->get('admin_orders_filters') : [];

        $form = $this->createForm(OrderFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $this->session->set('admin_orders_filters', $filters);
            $request->query->set('p', 1);

            return $this->redirectToRoute('admin_orders', [
                'filtered' => true,
            ]);
        }
        $this->requestStack->getSession()->set('order_return', $this->generateUrl('admin_orders', [
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

    /**
     * @Route("/admin/command/status/{orderHeader}/{status}", name="admin_order_status")
     */
    public function adminOrderValidate(
        OrdersPresenter $presenter,
        OrderPresenter $orderPresenter,
        PaginatorService $paginator,
        Request $request,
        OrderHeader $orderHeader,
        int $status
    ): Response {
        $filters = $this->session->get('admin_orders_filters');
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

    /**
     * @Route("/admin/commande/{orderHeader}", name="admin_order")
     */
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
}
