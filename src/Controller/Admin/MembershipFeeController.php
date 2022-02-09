<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MembershipFeeAmount;
use App\Form\Admin\MembershipFeeAmountType;
use App\Repository\MembershipFeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MembershipFeeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/tarifs', name: 'admin_membership_fee', methods: ['GET'])]
    public function adminList(
        MembershipFeeRepository $membershipFeeRepository
    ): Response {
        return $this->render('membershipFee/admin/list.html.twig', [
            'all_membership_fee' => $membershipFeeRepository->findAll(),
        ]);
    }

    #[Route('/admin/membership/fee/edit/{amount}', name: 'admin_membership_fee_edit', methods: ['GET', 'POST'])]
    public function adminEdit(
        Request $request,
        MembershipFeeAmount $amount
    ): Response {
        $form = $this->createForm(MembershipFeeAmountType::class, $amount, [
            'action' => $this->generateUrl(
                'admin_membership_fee_edit',
                [
                    'amount' => $amount->getId(),
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
