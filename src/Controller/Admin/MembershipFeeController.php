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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/tarif', name: 'admin_membership_fee')]
class MembershipFeeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/tarifs', name: '', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        MembershipFeeRepository $membershipFeeRepository
    ): Response {
        return $this->render('membershipFee/admin/list.html.twig', [
            'all_membership_fee' => $membershipFeeRepository->findAll(),
        ]);
    }

    #[Route('/edit/{amount}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(
        Request $request,
        MembershipFeeAmount $amount
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(MembershipFeeAmountType::class, $amount, [
            'action' => $this->generateUrl(
                'admin_membership_fee_edit',['amount' => $amount->getId(),]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $amount = $form->getData();
                $this->entityManager->persist($amount);
                $this->entityManager->flush();

                return $this->redirectToRoute('admin_membership_fee');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('membershipFee/admin/edit.modal.html.twig', [
            'amount' => $amount,
            'form' => $form->createView(),
        ], $response);
    }
}
