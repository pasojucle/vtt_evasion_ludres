<?php

namespace App\Controller;

use App\Form\OrderType;
use App\Entity\OrderHeader;
use App\Service\PdfService;
use App\ViewModel\UserPresenter;
use App\ViewModel\OrderPresenter;
use App\Repository\OrderLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderHeaderRepository;
use App\Service\Order\OrderLinesSetService;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private OrderPresenter $presenter;
    private OrderHeaderRepository $orderHeaderRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        OrderPresenter $presenter,
        OrderHeaderRepository $orderHeaderRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->presenter = $presenter;
        $this->orderHeaderRepository = $orderHeaderRepository;
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/mon_panier", name="order_edit")
     */
    public function orderEdit(
        OrderLinesSetService $orderLinesSetService,
        Request $request
    ): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            return $this->redirectToRoute('home');
        }
        $orderHeader = $this->orderHeaderRepository->findOneOrderByUser($user);
        if ($request->isXmlHttpRequest()) {
            $orderLinesSetService->execute($request);
        }
        $form = $this->createForm(OrderType::class, $orderHeader);

        if(!$request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $orderHeader = $form->getData();
                $orderHeader->setCreatedAt(new DateTime())
                    ->setStatus(OrderHeader::STATUS_ORDERED);
                $this->entityManager->persist($orderHeader);
                $this->entityManager->flush();

                return $this->redirectToRoute('order', ['orderHeader' => $orderHeader->getId()]);
            }
        }
        
        $this->presenter->present($orderHeader);

        return $this->render('order/edit.html.twig', [
            'order' => $this->presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ma_commande/{orderHeader}", name="order")
     */
    public function order(
        ?OrderHeader $orderHeader
    ): Response
    {
        $this->presenter->present($orderHeader);

        return $this->render('order/show.html.twig', [
            'order' => $this->presenter->viewModel()
        ]);
    }

    /**
     * @Route("/confirmation_commande/{orderHeader}", name="order_acknowledgement")
     */
    public function orderAcknowledgement(
        PdfService $pdfService,
        OrderHeader $orderHeader
    ): Response
    {
        $this->presenter->present($orderHeader);
        $orderAcknowledgement = $this->renderView('order/acknowledgement.html.twig', [
            'order' => $this->presenter->viewModel(),
        ]);
        $pdfFilepath = $pdfService->makePdf($orderAcknowledgement, 'order_acknowledgement_temp','../data/');

        $fileContent = file_get_contents($pdfFilepath);

        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'commande_vtt_evasion_ludres.pdf'
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
