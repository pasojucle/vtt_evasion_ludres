<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Admin\AbstractCrudController;
use App\Dto\DtoTransformer\OrderDtoTransformer;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\Member;
use App\Entity\OrderHeader;
use App\Form\OrderType;
use App\Repository\OrderHeaderRepository;
use App\Service\LogService;
use App\Service\MessageService;
use App\Service\PaginatorService;
use App\Service\PdfService;
use App\State\Order\Processor\OrderDeleteProcessor;
use App\State\Order\Provider\OrderDeleteProvider;
use App\UseCase\Order\OrderEdit;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractCrudController
{
    public function __construct(
        private OrderDtoTransformer $orderDtoTransformer,
        private OrderHeaderRepository $orderHeaderRepository,
        private RequestStack $requestStack,
        private MessageService $messageService,
    ) {
    }

    #[Route('/mon-compte/panier', name: 'order_edit', methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_LIST')]
    public function orderEdit(
        OrderEdit $orderEdit,
        Request $request
    ): Response {
        /** @var ?Member $member */
        $member = $this->getUser();
        $orderHeader = $this->orderHeaderRepository->findOneOrderInProgressByUser($member);
        $form = $this->createForm(OrderType::class, $orderHeader);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($url = $orderEdit->execute($form)) {
                return $this->redirect($url);
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

        $logService->writeFromEntity($orderHeader, $orderHeader->getMember());
        
        return $this->render('order/show.html.twig', [
            'order' => $this->orderDtoTransformer->fromEntity($orderHeader),
            'message' => match ($orderHeader->getStatus()) {
                OrderStatusEnum::ORDERED => $this->messageService->getMessageById('ORDER_WAITING_VALIDATE_MESSAGE'),
                OrderStatusEnum::CANCELED => $this->messageService->getMessageById('ORDER_CANCELED_MESSAGE'),
                default => $this->messageService->getMessageById('ORDER_ACKNOWLEDGEMENT_MESSAGE')
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
            'message' => $this->messageService->getMessageById('ORDER_ACKNOWLEDGEMENT_MESSAGE'),
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
        OrderDeleteProcessor $processor,
        OrderDeleteProvider $provider,
        OrderHeader $orderHeader
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $orderHeader,
            $provider,
            $processor
        );
    }

    #[Route('/mon-compte/commandes', name: 'user_orders', methods: ['GET'])]
    #[IsGranted('PRODUCT_LIST')]
    public function userOrders(
        PaginatorService $paginator,
        Request $request
    ): Response {
        /** @var Member $member */
        $member = $this->getUser();
        $query = $this->orderHeaderRepository->findOrdersByMemberQuery($member);
        $orders = $paginator->paginateFromRequest($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $this->requestStack->getSession()->set('order_return', $this->generateUrl('user_orders'));

        return $this->render('order/list.html.twig', [
            'orders' => $this->orderDtoTransformer->fromEntities($orders),
        ]);
    }
}
