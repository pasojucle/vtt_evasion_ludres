<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\ContentDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\Content;
use App\Form\Admin\ContentType;
use App\Form\Admin\HomeBackgroundsType;
use App\Repository\ContentRepository;
use App\Repository\MessageRepository;
use App\Repository\ParameterRepository;
use App\Service\MessageService;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\Service\ProjectDirService;
use App\Service\UploadService;
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
    public const HOME_TAB_FLASH = 0;
    public const HOME_TAB_CONTENT = 1;
    public const HOME_TAB_BACKGROUNDS = 2;

    private const HOME_TABS = [
        self::HOME_TAB_FLASH => 'content.type.flash',
        self::HOME_TAB_CONTENT => 'content.type.content',
        self::HOME_TAB_BACKGROUNDS => 'content.type.backgrounds',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderByService $orderByService,
        private ContentRepository $contentRepository,
        private PaginatorDtoTransformer $paginatorDtoTransformer,
    ) {
    }

    #[Route('/page/accueil/contenus/{tab}', name: 'admin_home_contents', methods: ['GET', 'POST'], defaults:['route' => 'home', 'tab' => self::HOME_TAB_FLASH])]
    #[IsGranted('ROLE_ADMIN')]
    public function listHome(
        PaginatorService $paginator,
        Request $request,
        ?string $route,
        int $tab
    ): Response {
        $isFlash = self::HOME_TAB_FLASH === $tab;

        $form = $this->createForm(HomeBackgroundsType::class, $this->contentRepository->findOneByRoute('home'));
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Enregistrement effectué.');
        }

        $query = $this->contentRepository->findContentQuery($route, $isFlash);
        $contents = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('content/admin/home_contents.html.twig', [
            'contents' => $contents,
            'form' => $form->createView(),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($contents, ['route' => $route, 'tab' => $tab]),
            'current_route' => $route,
            'is_flash' => $isFlash,
            'tabs' => self::HOME_TABS,
            'tab' => $tab,
        ]);
    }

    #[Route('/contenus', name: 'admin_contents', methods: ['GET'], defaults:['route' => null, 'isFlash' => false])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(
        PaginatorService $paginator,
        Request $request,
        ?string $route,
        bool $isFlash
    ): Response {
        $query = $this->contentRepository->findContentQuery($route, $isFlash);
        $contents = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('content/admin/list.html.twig', [
            'contents' => $contents,
            'paginator' => $this->paginatorDtoTransformer->fromEntities($contents, ['route' => $route, 'isFlash' => $isFlash]),
            'current_route' => $route,
            'is_flash' => $isFlash,
        ]);
    }


    #[Route('/page/accueil/contenu/{content}', name: 'admin_home_content_edit', methods: ['GET', 'POST'], defaults:['content' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminHomeContentEdit(
        Request $request,
        ContentDtoTransformer $contentDtoTransformer,
        UploadService $uploadService,
        ?Content $content
    ): Response {
        $form = $this->createForm(ContentType::class, $content);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $content = $form->getData();
            if (null === $content->getOrderBy()) {
                $content->setOrderBy(0);
                $order = $this->contentRepository->findNexOrderByRoute($content->getRoute(), $content->isFlash());
                $content->setOrderBy($order);
            }

            if ($request->files->get('content')) {
                $file = $request->files->get('content')['file'];
                if ($file) {
                    $content->setFileName($uploadService->uploadFile($file));
                }
            }
            $this->entityManager->persist($content);
            $this->entityManager->flush();

            $contents = $this->contentRepository->findByRoute('home', !$content->isFlash());
            $this->orderByService->resetOrders($contents);

            return $this->redirectToRoute('admin_home_contents', [
                'route' => $content->getRoute(),
                'tab' => (int) $content->isFlash() ? self::HOME_TAB_FLASH : self::HOME_TAB_CONTENT,
            ]);
        }

        return $this->render('content/admin/edit.html.twig', [
            'content' => $contentDtoTransformer->fromEntity($content),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/contenu/{content}', name: 'admin_content_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminContentEdit(
        Request $request,
        ContentDtoTransformer $contentDtoTransformer,
        UploadService $uploadService,
        MessageRepository $messageRepository,
        Content $content
    ): Response {
        $form = $this->createForm(ContentType::class, $content);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $content = $form->getData();
            if (null === $content->getOrderBy()) {
                $content->setOrderBy(0);
                $order = $this->contentRepository->findNexOrderByRoute($content->getRoute(), $content->isFlash());
                $content->setOrderBy($order);
            }

            if ($request->files->get('content')) {
                $file = $request->files->get('content')['file'];
                $content->setFileName($uploadService->uploadFile($file));
            }

            $this->entityManager->persist($content);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_contents');
        }

        return $this->render('content/admin/edit.html.twig', [
            'content' => $contentDtoTransformer->fromEntity($content),
            'form' => $form->createView(),
            'settings' => [
                'messages' => (empty($content->getParameters())) ? $messageRepository->findByNames($content->getParameters()) : null,
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
        $isFlash = $content->IsFlash();

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($content);
            $this->entityManager->flush();

            $contents = $this->contentRepository->findByRoute($route, $isFlash);
            $this->orderByService->ResetOrders($contents);

            return $this->redirectToRoute('admin_home_contents', [
                'isFlash' => (int) $isFlash,
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
        $isFlash = $content->isFlash();
        $newOrder = (int) $request->request->get('newOrder');
        $contents = $this->contentRepository->findByRoute($route, $isFlash);

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
