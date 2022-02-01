<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use App\Service\LinkService;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class LinkController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private LinkRepository $linkRepository;

    private OrderByService $orderByService;

    public function __construct(
        EntityManagerInterface $entityManager,
        LinkRepository $linkRepository,
        OrderByService $orderByService
    ) {
        $this->entityManager = $entityManager;
        $this->linkRepository = $linkRepository;
        $this->orderByService = $orderByService;
    }

    /**
     * @Route("/liens", name="links")
     */
    public function list(
        LinkService $linkService
    ): Response {
        $links = $this->linkRepository->findByPosition(Link::POSITION_LINK_PAGE);

        return $this->render('link/list.html.twig', [
            'links' => $links,
        ]);
    }

    /**
     * @Route("/admin/liens/{position}", name="admin_links", defaults={"position" = 1})
     */
    public function adminList(
        PaginatorService $paginator,
        Request $request,
        int $position
    ): Response {
        $query = $this->linkRepository->findLinkQuery($position);
        $links = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('link/admin/list.html.twig', [
            'links' => $links,
            'lastPage' => $paginator->lastPage($links),
            'current_position' => $position,
            'current_filters' => [
                'position' => (int) $position,
            ],
        ]);
    }

    /**
     * @Route("/admin/lien/{link}", name="admin_link_edit", defaults={"link"=null})
     */
    public function adminLinkEdit(
        Request $request,
        LinkService $linkService,
        SluggerInterface $slugger,
        ?Link $link
    ): Response {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $link = $form->getData();

            $isNew = null === $link->getTitle() && null === $link->getDescription() && null === $link->getImage();
            if (null !== $link->getUrl() && ($isNew || ($form->has('search') && $form->get('search')->isClicked()))) {
                $data = $linkService->getUrlData($link->getUrl());

                if ($data) {
                    $link->setTitle($data['title'])
                        ->setDescription($data['description'])
                        ->setImage($data['image'])
                    ;
                }
            }
            if ($request->files->get('link')) {
                $pictureFile = $request->files->get('link')['imageFile'];
                if ($pictureFile) {
                    $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();
                    if (!is_dir($this->getParameter('uploads_directory_path'))) {
                        mkdir($this->getParameter('uploads_directory_path'));
                    }

                    try {
                        $pictureFile->move(
                            $this->getParameter('uploads_directory_path'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                    $link->setImage($newFilename);
                }
            }
            if (null === $link->getOrderBy() && null !== $link->getPosition()) {
                $order = $this->linkRepository->findNexOrderByPosition($link->getPosition());
                $link->setOrderBy($order);
            }
            $this->entityManager->persist($link);
            $this->entityManager->flush();
            if ($isNew) {
                return $this->redirectToRoute('admin_link_edit', [
                    'link' => $link->getId(),
                ]);
            }

            return $this->redirectToRoute('admin_links', [
                'position' => $link->getPosition(),
            ]);
        }

        return $this->render('link/admin/edit.html.twig', [
            'link' => $link,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/supprimer/lien/{link}", name="admin_link_delete")
     */
    public function adminLinkDelete(
        Request $request,
        Link $link
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_link_delete',
                [
                    'link' => $link->getId(),
                ]
            ),
        ]);
        $position = $link->getPosition();

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($link);
            $this->entityManager->flush();

            $links = $this->linkRepository->findByPosition($position);
            $this->orderByService->ResetOrders($links);

            return $this->redirectToRoute('admin_links', [
                'position' => $position,
            ]);
        }

        return $this->render('link/admin/delete.modal.html.twig', [
            'link' => $link,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/ordonner/lien/{link}", name="admin_link_order", options={"expose"=true},)
     */
    public function adminLinkOrder(
        Request $request,
        Link $link
    ): Response {
        $position = $link->getPosition();
        $newOrder = $request->request->get('newOrder');
        $links = $this->linkRepository->findByPosition($position);

        $this->orderByService->setNewOrders($link, $links, $newOrder);

        return new Response();
    }
}
