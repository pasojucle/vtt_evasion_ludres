<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Agreement;
use App\Form\Admin\AgreementType;
use App\Service\OrderByService;
use App\Repository\AgreementRepository;
use App\UseCase\Agreement\AddAgreement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name: 'admin_agreement_')]
class AgreementController extends AbstractController
{
    public function __construct(
        private readonly AgreementRepository $agreementRepository,
        private readonly OrderByService $orderByService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/autorisations', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(): Response {

        return $this->render('agreement/admin/list.html.twig', [
            'agreements' => $this->agreementRepository->findAll(),
        ]);
    }

    #[Route('/admin/agreement', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function add(
        Request $request,
        AddAgreement $addAgreement,
    ): Response {
        $form = $this->createForm(AgreementType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $addAgreement->execute($form, $request);

            return $this->redirectToRoute('admin_agreement_list');
        }

        return $this->render('agreement/admin/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/agreement/{agreement}', name: 'edit', methods: ['GET', 'POST'], requirements:['agreement' => '\w+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        Agreement $agreement
    ): Response {
        $form = $this->createForm(AgreementType::class, $agreement);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
                
            return $this->redirectToRoute('admin_agreement_list');
        }

        return $this->render('agreement/admin/edit.html.twig', [
            'agreement' => $agreement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ordonner/{agreement}', name: 'order', methods: ['POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function order(
        Request $request,
        Agreement $agreement
    ): Response {
        $newOrder = (int) $request->request->get('newOrder');
        $agreements = $this->agreementRepository->findAll();

        $this->orderByService->setNewOrders($agreement, $$agreements, $newOrder);

        return new Response();
    }

    #[Route('/admin/active/agreement/{agreement}', name: 'enable', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function disbaled(
        Request $request,
        Agreement $agreement
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $agreement->setEnabled(!$agreement->isEnabled());
            $this->entityManager->persist($agreement);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_agreement_list');
        }

        return $this->render('agreement/admin/enable.modal.html.twig', [
            'agreement' => $agreement,
            'form' => $form->createView(),
        ]);
    }

}