<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Background;
use App\Form\Admin\BackgroundType;
use App\Repository\BackgroundRepository;
use App\Service\PaginatorService;
use App\UseCase\Background\EditBackground;
use App\ViewModel\Background\BackgroundPresenter;
use App\ViewModel\Background\BackgroundsPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class BackgroundController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BackgroundRepository $backgroundRepository
    ) {
    }

    #[Route('/images_de_fond', name: 'admin_background_list', methods: ['GET'])]
    public function adminList(PaginatorService $paginator, Request $request, BackgroundsPresenter $presenter): Response
    {
        $query = $this->backgroundRepository->findAllQuery();
        $backgrounds = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $presenter->present($backgrounds);

        return $this->render('background/admin/list.html.twig', [
            'backgrounds' => $presenter->viewModel()->backgrounds,
            'lastPage' => $paginator->lastPage($backgrounds),
            'count' => $paginator->total($backgrounds),
        ]);
    }

    #[Route('/image_de_fond/{background}', name: 'admin_background_edit', defaults:['background' => null], methods: ['GET', 'post'])]
    public function adminEdit(Request $request, BackgroundPresenter $presenter, EditBackground $editBackground, ?Background $background): Response
    {
        $currentFilename = $background?->getFilename();
        $form = $this->createForm(BackgroundType::class, $background);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editBackground->execute($form->getData(), $request, $currentFilename);

            return $this->redirectToRoute('admin_background_list');
        }

        $presenter->present($background);

        return $this->render('background/admin/edit.html.twig', [
            'background' => $presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/supprimer/image_de_fond/{background}', name: 'admin_background_delete', methods: ['GET', 'POST'])]
    public function adminBackgroundDelete(
        Request $request,
        Background $background
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_background_delete',
                [
                    'background' => $background->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($background);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_background_list');
        }

        return $this->render('background/admin/delete.modal.html.twig', [
            'background' => $background,
            'form' => $form->createView(),
        ]);
    }
}
