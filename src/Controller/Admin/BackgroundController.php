<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BackgroundDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\Background;
use App\Form\Admin\BackgroundType;
use App\Repository\BackgroundRepository;
use App\Service\PaginatorService;
use App\UseCase\Background\EditBackground;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class BackgroundController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BackgroundRepository $backgroundRepository,
        private BackgroundDtoTransformer $backgroundDtoTransformer
    ) {
    }

    #[Route('/images_de_fond', name: 'admin_background_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        Request $request,
    ): Response {
        $query = $this->backgroundRepository->findAllQuery();
        $backgrounds = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('background/admin/list.html.twig', [
            'backgrounds' => $this->backgroundDtoTransformer->fromEntities($backgrounds),
            'paginator' => $paginatorDtoTransformer->fromEntities($backgrounds),
        ]);
    }

    #[Route('/image_de_fond/{background}', name: 'admin_background_edit', defaults:['background' => null], methods: ['GET', 'post'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(Request $request, EditBackground $editBackground, ?Background $background): Response
    {
        $currentFilename = $background?->getFilename();
        $form = $this->createForm(BackgroundType::class, $background);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editBackground->execute($form->getData(), $request, $currentFilename);

            return $this->redirectToRoute('admin_background_list');
        }

        return $this->render('background/admin/edit.html.twig', [
            'background' => $this->backgroundDtoTransformer->fromEntity($background),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/supprimer/image_de_fond/{background}', name: 'admin_background_delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBackgroundDelete(
        Request $request,
        Background $background
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->remove($background);
                $this->entityManager->flush();

                return $this->redirectToRoute('admin_background_list');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('background/admin/delete.modal.html.twig', [
            'background' => $background,
            'form' => $form->createView(),
            'type' => 'destructive',
        ], $response);
    }
}
