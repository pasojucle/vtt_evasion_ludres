<?php

namespace App\Controller;

use App\Entity\Content;
use App\Form\ContentType;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\Repository\ContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private OrderByService $orderByService;
    private ContentRepository $contentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        OrderByService $orderByService,
        ContentRepository $contentRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->orderByService = $orderByService;
        $this->contentRepository = $contentRepository;
    }
    /**
     * @Route("/admin/page/accueil/contenus/{isFlash}", name="admin_home_contents", defaults={"route"="home", "isFlash"=true})
     * @Route("/admin/contenus/{route}", name="admin_contents", defaults={"route"=null, "isFlash"=false})
     */
    public function list(
        PaginatorService $paginator,
        Request $request,
        ?string $route,
        bool $isFlash
    ): Response
    {
        if (null === $route) {
            $route = 'registration_detail';
        }
        $query =  $this->contentRepository->findContentQuery($route, $isFlash);
        $contents =  $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('content/admin/list.html.twig', [
            'contents' => $contents,
            'lastPage' => $paginator->lastPage($contents),
            'current_route' => $route,
            'is_flash' => $isFlash,
            'current_filters' => ['route' => $route, 'isFlash' => $isFlash],
        ]);
    }


    /**
     * @Route("/admin/page/accueil/contenu/{content}", name="admin_home_content_edit", defaults={"content"=null})
     * @Route("/admin/contenu/{content}", name="admin_content_edit")
     */
    public function adminContentEdit(
        Request $request,
        ?Content $content
    ): Response
    {
        $form = $this->createForm(ContentType::class, $content);
        
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $content = $form->getData();
            $content->setOrderBy(0);
            $order = $this->contentRepository->findNexOrderByRoute($content->getRoute(), $content->isFlash());
            $content->setOrderBy($order);
            $this->entityManager->persist($content);
            $this->entityManager->flush();

            if ('home' === $content->getRoute()) {
                $contents = $this->contentRepository->findByRoute('home', !$content->isFlash());
                $this->orderByService->resetOrders($contents);

                return $this->redirectToRoute('admin_home_contents', ['route' => $content->getRoute(), 'isFlash' => (int) $content->isFlash()]);
            }

            return $this->redirectToRoute('admin_contents', ['route' => $content->getRoute()]);
        }

        return $this->render('content/admin/edit.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/supprimer/contenu/{content}", name="admin_content_delete")
     */
    public function adminContentDelete(
        Request $request,
        Content $content
    ): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('admin_content_delete', 
                [
                    'content'=> $content->getId(),
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

            return $this->redirectToRoute('admin_contents', ['route' => $route, 'isFlash' => $isFlash]);
        }

        return $this->render('content/admin/delete.modal.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/ordonner/contenu/{content}", name="admin_content_order", options={"expose"=true},)
     */
    public function adminContentOrder(
        Request $request,
        Content $content
    ): Response
    {
        $route = $content->getRoute();
        $isFlash = $content->isFlash();
        $newOrder = $request->request->get('newOrder');
        $contents = $this->contentRepository->findByRoute($route, $isFlash);

        $this->orderByService->setNewOrders($content, $contents, $newOrder);

        return new Response();
    }
}
