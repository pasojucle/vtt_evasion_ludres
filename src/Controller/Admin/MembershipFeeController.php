<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\Admin\ProductType;
use App\Service\PaginatorService;
use App\Entity\MembershipFeeAmount;
use App\ViewModel\ProductPresenter;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Admin\MembershipFeeAmountType;
use App\Repository\MembershipFeeRepository;
use App\Service\Product\ProductEditService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MembershipFeeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

     /**
     * @Route("/admin/tarifs", name="admin_membership_fee")
     */
    public function adminList(
        MembershipFeeRepository $membershipFeeRepository
    ): Response
    {

        return $this->render('membershipFee/admin/list.html.twig', [
            'all_membership_fee' => $membershipFeeRepository->findAll(),
        ]);
    }

     /**
     * @Route("/admin/membership/fee/edit/{amount}", name="admin_membership_fee_edit")
     */
    public function adminEdit(
        Request $request,
        MembershipFeeAmount $amount
    ): Response
    {
        $form = $this->createForm(MembershipFeeAmountType::class, $amount, [
            'action' => $this->generateUrl('admin_membership_fee_edit', 
                [
                    'amount'=> $amount->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $amount = $form->getData();
            $this->entityManager->persist($amount);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_membership_fee');
        }

        return $this->render('membershipFee/admin/edit.modal.html.twig', [
            'amount' => $amount,
            'form' => $form->createView(),
        ]);
    }
}