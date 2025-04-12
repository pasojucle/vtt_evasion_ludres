<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\OrderDtoTransformer;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Entity\User;
use App\Form\OrderType;
use App\Repository\OrderHeaderRepository;
use App\Service\LogService;
use App\Service\MessageService;
use App\Service\Order\OrderLinesSetService;
use App\Service\Order\OrderValidateService;
use App\Service\PaginatorService;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractController
{
    public function __construct(
        private OrderDtoTransformer $orderDtoTransformer,
        private OrderHeaderRepository $orderHeaderRepository,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private MessageService $messageService,
    ) {
    }

    #[Route('/mon-compte/panier', name: 'order_edit', methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_LIST')]
    public function orderEdit(
        OrderLinesSetService $orderLinesSetService,
        OrderValidateService $orderValidateService,
        Request $request
    ): Response {
        /** @var ?User $user */
        $user = $this->getUser();
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

    #[Route('/mon-compte/commande/{orderHeader}', name: 'order', methods: ['GET'])]
    #[IsGranted('PRODUCT_EDIT', 'orderHeader')]
    public function order(
        LogService $logService,
        ?OrderHeader $orderHeader
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $logService->writeFromEntity($orderHeader, $orderHeader->getUser());
        
        return $this->render('order/show.html.twig', [
            'order' => $this->orderDtoTransformer->fromEntity($orderHeader),
            'message' => match ($orderHeader->getStatus()) {
                OrderStatusEnum::ORDERED => $this->messageService->getMessageByName('ORDER_WAITING_VALIDATE_MESSAGE'),
                OrderStatusEnum::CANCELED => $this->messageService->getMessageByName('ORDER_CANCELED_MESSAGE'),
                default => $this->messageService->getMessageByName('ORDER_ACKNOWLEDGEMENT_MESSAGE')
            },
        ]);
    }

    #[Route('/confirmation-commande/{orderHeader}', name: 'order_acknowledgement', methods: ['GET'])]
    #[IsGranted('PRODUCT_EDIT', 'orderHeader')]
    public function orderAcknowledgement(
        PdfService $pdfService,
        OrderHeader $orderHeader,
        ParameterBagInterface $parameterBag
    ): Response {
        $orderAcknowledgement = $this->renderView('order/acknowledgement.html.twig', [
            'order' => $this->orderDtoTransformer->fromEntity($orderHeader),
            'message' => $this->messageService->getMessageByName('ORDER_ACKNOWLEDGEMENT_MESSAGE'),
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
    #[IsGranted('PRODUCT_EDIT', 'orderHeader')]
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
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $orderHeader->setStatus(OrderStatusEnum::CANCELED);
            $this->entityManager->persist($orderHeader);
            $this->entityManager->flush();

            return $this->redirect($this->requestStack->getSession()->get('order_return'));
        }

        return $this->render('order/delete.modal.html.twig', [
            'order_header' => $this->orderDtoTransformer->fromEntity($orderHeader),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mon-compte/commandes', name: 'user_orders', methods: ['GET'])]
    #[IsGranted('PRODUCT_LIST')]
    public function userOrders(
        PaginatorService $paginator,
        Request $request
    ): Response {
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
