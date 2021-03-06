<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ModalWindow;
use App\Form\Admin\ModalWindowType;
use App\Repository\ModalWindowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModalWindowController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin/popup', name: 'admin_modal_window_list', methods: ['GET'])]
    public function list(ModalWindowRepository $modalWindowRepository): Response
    {
        return $this->render('modal_window/admin/list.html.twig', [
            'modal_window_list' => $modalWindowRepository->findAllDesc(),
        ]);
    }

    #[Route('/admin/fenêtre/modale/edit/{modalWindow}', name: 'admin_modal_window_edit', methods: ['GET', 'POST'], defaults:['modalWindow' => null])]
    public function edit(Request $request, ?ModalWindow $modalWindow): Response
    {
        $form = $this->createForm(ModalWindowType::class, $modalWindow);
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

    #[Route('/admin/modal/window/toggle/{modalWindow}', name: 'admin_modal_window_toggle_disable', methods: ['GET'])]
    public function toggle(ModalWindow $modalWindow): Response
    {
        $modalWindow->setIsDisabled(!$modalWindow->isDisabled());
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_modal_window_list');
    }
}
