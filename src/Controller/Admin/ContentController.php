<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\ContentDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\Content;
use App\Entity\Enum\ContentKindEnum;
use App\Form\Admin\ContentType;
use App\Form\Admin\HomeBackgroundsType;
use App\Repository\ContentRepository;
use App\Repository\MessageRepository;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\Service\ProjectDirService;
use App\UseCase\Content\SetContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param')]
class ContentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderByService $orderByService,
        private readonly ContentRepository $contentRepository,
        private readonly PaginatorDtoTransformer $paginatorDtoTransformer,
        private readonly ContentDtoTransformer $contentDtoTransformer,
        private readonly SetContent $setContent,
    ) {
    }

    #[Route('/page/accueil/contenus/{kind}', name: 'admin_home_contents', methods: ['GET', 'POST'], defaults:['route' => 'home', 'kind' => ContentKindEnum::HOME_FLASH->value])]
    #[IsGranted('ROLE_ADMIN')]
    public function listHome(
        PaginatorService $paginator,
        Request $request,
        ?string $route,
        ContentKindEnum $kind
    ): Response {
        $form = $this->createForm(HomeBackgroundsType::class, $this->contentRepository->findOneByRoute('home'));
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Enregistrement effectué.');
        }

        $query = $this->contentRepository->findContentQuery($route, $kind);
        $contents = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('content/admin/home_contents.html.twig', [
            'contents' => $contents,
            'form' => $form->createView(),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($contents, ['route' => $route, 'kind' => $kind->value]),
            'current_route' => $route,
            'kind' => $kind,
        ]);
    }

    #[Route('/contenus', name: 'admin_contents', methods: ['GET'], defaults:['route' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(
        PaginatorService $paginator,
        Request $request,
        ?string $route
    ): Response {
        $query = $this->contentRepository->findContentQuery($route, null);
        $contents = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('content/admin/list.html.twig', [
            'contents' => $this->contentDtoTransformer->fromEntities($contents)->contents,
            'paginator' => $this->paginatorDtoTransformer->fromEntities($contents, ['route' => $route]),
            'current_route' => $route,
        ]);
    }

    #[Route('/page/accueil/contenu/{content}', name: 'admin_home_content_edit', methods: ['GET', 'POST'], defaults:['content' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminHomeContentEdit(
        Request $request,
        ?Content $content
    ): Response {
        $form = $this->createForm(ContentType::class, $content, [
            'allowed_kinds' => [ContentKindEnum::HOME_FLASH, ContentKindEnum::HOME_CONTENT],
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->setContent->execute($form, $request);
            $contents = $this->contentRepository->findByRoute('home', $content->getKind());
            $this->orderByService->resetOrders($contents);

            return $this->redirectToRoute('admin_home_contents', [
                'route' => $content->getRoute(),
                'kind' => $content->getKind()->value,
            ]);
        }

        return $this->render('content/admin/edit.html.twig', [
            'content' => $this->contentDtoTransformer->fromEntity($content),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/contenu/{content}', name: 'admin_content_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminContentEdit(
        Request $request,
        ContentDtoTransformer $contentDtoTransformer,
        MessageRepository $messageRepository,
        Content $content
    ): Response {
        $form = $this->createForm(ContentType::class, $content, [
            'allowed_kinds' => [ContentKindEnum::CARROUSEL_AND_TEXT, ContentKindEnum::VIDEO_AND_TEXT],
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->setContent->execute($form, $request);

            return $this->redirectToRoute('admin_contents');
        }

        return $this->render('content/admin/edit.html.twig', [
            'content' => $contentDtoTransformer->fromEntity($content),
            'form' => $form->createView(),
            'settings' => [
                'messages' => (!empty($content->getParameters())) ? $messageRepository->findByNames($content->getParameters()) : null,
            ],
        ]);
    }

    #[Route('/supprimer/contenu/{content}', name: 'admin_content_delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminContentDelete(
        Request $request,
        Content $content
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_content_delete',
                [
                    'content' => $content->getId(),
                ]
            ),
        ]);
        $route = $content->getRoute();
        $kind = $content->getKind();

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($content);
            $this->entityManager->flush();

            $contents = $this->contentRepository->findByRoute($route, $kind);
            $this->orderByService->ResetOrders($contents);

            return $this->redirectToRoute('admin_home_contents', [
                'kind' => $kind,
            ]);
        }

        return $this->render('content/admin/delete.modal.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ordonner/contenu/{content}', name: 'admin_content_order', methods: ['POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminContentOrder(
        Request $request,
        Content $content
    ): Response {
        $route = $content->getRoute();
        $newOrder = (int) $request->request->get('newOrder');
        $contents = $this->contentRepository->findByRoute($route, $content->getKind());

        $this->orderByService->setNewOrders($content, $contents, $newOrder);

        return new Response();
    }

    #[Route('/file/content/delete/{content}', name: 'admin_content_file_delete', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminFileContentDelete(
        ProjectDirService $projectDir,
        Content $content
    ): Response {
        $filename = $content->getFilename();
        $filesystem = new Filesystem();
        $filePath = $projectDir->path('uploads_directory_path', $filename);
        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }
        $content->setFilename(null);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_home_content_edit', [
            'content' => $content->getId(),

        ]);
    }
}
