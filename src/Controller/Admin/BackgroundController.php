<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Background;
use App\Service\UploadService;
use App\Service\PaginatorService;
use App\Form\Admin\BackgroundType;
use App\Repository\BackgroundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\ViewModel\Background\BackgroundPresenter;
use App\ViewModel\Background\BackgroundsPresenter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class BackgroundController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BackgroundRepository $backgroundRepository,
        private UploadService $uploadService
    ) {
    }

    #[Route('/images_de_fond', name: 'admin_background_list', methods: ['GET'])]
    public function adminList(PaginatorService $paginator, Request $request, BackgroundsPresenter $presenter)
    {
        $query = $this->backgroundRepository->findAllQuery();
        $backgrounds = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $presenter->present($backgrounds);
        return $this->render('background/admin/list.html.twig', [
            'backgrounds' => $presenter->viewModel()->backgrounds,
            'lastPage' => $paginator->lastPage($backgrounds),
        ]);
    }

    #[Route('/image_de_fond/{background}', name: 'admin_background_edit', defaults:['background' => null], methods: ['GET', 'post'])]
    public function adminEdit(Request $request, BackgroundPresenter $presenter, ?Background $background)
    {
        $form = $this->createForm(BackgroundType::class, $background);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $background = $form->getData();
            if ($request->files->get('background')) {
                dump($request->files->get('background'));
                $file = $request->files->get('background')['backgroundFile'];
                $background->setFileName($this->uploadService->uploadFile($file));
            }

            $this->entityManager->persist($background);
            $this->entityManager->flush();
        }

        $presenter->present($background);
        return $this->render('background/admin/edit.html.twig', [
            'background' => $presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }
}