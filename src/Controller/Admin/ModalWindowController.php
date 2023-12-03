<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ModalWindow;
use App\Form\Admin\ModalWindowType;
use App\Repository\ModalWindowRepository;
use App\Repository\ParameterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/popup', name: 'admin_modal_window_')]
class ModalWindowController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('s', name: 'list', methods: ['GET'])]
    #[IsGranted('MODAL_WINDOW_LIST')]
    public function list(ModalWindowRepository $modalWindowRepository, ParameterRepository $parameterRepository): Response
    {
        return $this->render('modal_window/admin/list.html.twig', [
            'modal_window_list' => $modalWindowRepository->findAllDesc(),
            'settings' => [
                'parameters' => $parameterRepository->findByParameterGroupName('MODAL'),
                'routes' => [],
            ],
        ]);
    }

    #[Route('/', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('MODAL_WINDOW_ADD')]
    public function add(Request $request): Response
    {
        $modalWindow = null;
        $form = $this->createForm(ModalWindowType::class, null);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $modalWindow = $form->getData();
            $this->entityManager->persist($modalWindow);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_modal_window_list');
        }

        return $this->render('modal_window/admin/edit.html.twig', [
            'modal_window' => $modalWindow,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{modalWindow}', name: 'edit', methods: ['GET', 'POST'], requirements:['modalWindow' => '\d+'])]
    #[IsGranted('MODAL_WINDOW_EDIT', 'modalWindow')]
    public function edit(Request $request, ?ModalWindow $modalWindow): Response
    {
        $form = $this->createForm(ModalWindowType::class, $modalWindow);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_modal_window_list');
        }

        return $this->render('modal_window/admin/edit.html.twig', [
            'modal_window' => $modalWindow,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/toggle/{modalWindow}', name: 'toggle_disable', methods: ['GET'])]
    #[IsGranted('MODAL_WINDOW_EDIT', 'modalWindow')]
    public function toggle(ModalWindow $modalWindow): Response
    {
        $modalWindow->setIsDisabled(!$modalWindow->isDisabled());
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_modal_window_list');
    }
}
