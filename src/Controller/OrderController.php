<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\OrderHeader;
use App\Repository\OrderHeaderRepository;
use App\ViewModel\OrderPresenter;
use App\ViewModel\ProductsPresenter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    /**
     * @Route("/mon_panier", name="order")
     */
    public function order(
        OrderPresenter $presenter,
        OrderHeaderRepository $orderHeaderRepository
    ): Response
    {
        $user = $this->getUser();
        $orderHeader = $orderHeaderRepository->findOneOrderByUserAndStatus($user, OrderHeader::STATUS_VALIDED);
        $presenter->present($orderHeader);

        return $this->render('order/show.html.twig', [
            'view' => $presenter->viewModel(),
            // 'form' => $form->createView(),
        ]);
    }
}
