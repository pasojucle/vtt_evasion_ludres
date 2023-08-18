<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\OrderDtoTransformer;
use App\Entity\OrderHeader;
use App\Entity\User;
use App\Form\OrderType;
use App\Repository\OrderHeaderRepository;
use App\Service\Order\OrderLinesSetService;
use App\Service\Order\OrderValidateService;
use App\Service\PaginatorService;
use App\Service\ParameterService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    public function __construct(
        private OrderDtoTransformer $orderDtoTransformer,
        private OrderHeaderRepository $orderHeaderRepository,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private ParameterService $parameterService
    ) {
    }

    #[Route('/mon-panier', name: 'order_edit', methods: ['GET', 'POST'])]
    public function orderEdit(
        OrderLinesSetService $orderLinesSetService,
        OrderValidateService $orderValidateService,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var ?User $user */
        $user = $this->getUser();
        if (null === $user) {
            return $this->redirectToRoute('home');
        }
        $orderHeader = $this->orderHeaderRepository->findOneOrderInProgressByUser($user);
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

        return $this->render('order/edit.html.twig', [
            'order' => $this->orderDtoTransformer->fromEntity($orderHeader, $form),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ma-commande/{orderHeader}', name: 'order', methods: ['GET'])]
    public function order(
        ?OrderHeader $orderHeader
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('order/show.html.twig', [
            'order' => $this->orderDtoTransformer->fromEntity($orderHeader),
            'message' => $this->parameterService->getParameterByName('ORDER_ACKNOWLEDGEMENT_MESSAGE'),
        ]);
    }

    #[Route('/confirmation-commande/{orderHeader}', name: 'order_acknowledgement', methods: ['GET'])]
    public function orderAcknowledgement(
        PdfService $pdfService,
        OrderHeader $orderHeader,
        ParameterBagInterface $parameterBag
    ): Response {
        $orderAcknowledgement = $this->renderView('order/acknowledgement.html.twig', [
            'order' => $this->orderDtoTransformer->fromEntity($orderHeader),
            'message' => $this->parameterService->getParameterByName('ORDER_ACKNOWLEDGEMENT_MESSAGE'),
        ]);
        $pdfFilepath = $pdfService->makePdf($orderAcknowledgement, 'order_acknowledgement_temp', $parameterBag->get('tmp_directory_path'));

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

    #[Route('/commande/supprimer/{orderHeader}', name: 'order_delete', methods: ['GET', 'POST'])]
    public function orderDelete(
        Request $request,
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

        return $this->render('order/delete.modal.html.twig', [
            'order_header' => $this->orderDtoTransformer->fromEntity($orderHeader),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mes-commandes', name: 'user_orders', methods: ['GET'])]
    public function userOrders(
        PaginatorService $paginator,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();
        $query = $this->orderHeaderRepository->findOrdersByUserQuery($user);
        $orders = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $this->requestStack->getSession()->set('order_return', $this->generateUrl('user_orders'));

        return $this->render('order/list.html.twig', [
            'orders' => $this->orderDtoTransformer->fromEntities($orders),
        ]);
    }
}
