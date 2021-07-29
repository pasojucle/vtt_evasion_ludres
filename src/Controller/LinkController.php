<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Service\LinkService;
use App\Service\PaginatorService;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class LinkController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LinkRepository $linkRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LinkRepository $linkRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->linkRepository = $linkRepository;
    }

    /**
     * @Route("/liens", name="links")
     */
    public function list(
        LinkService $linkService
    ): Response
    {
        $links = $this->linkRepository->findByPosition(Link::POSITION_LINK_PAGE);

        return $this->render('link/list.html.twig', [
            'links' => $links,
        ]);
    }

        /**
     * @Route("/admin/liens", name="admin_links")
     */
    public function adminList(
        PaginatorService $paginator,
        Request $request
    ): Response
    {
        $filters = null;

        $query =  $this->linkRepository->findLinkQuery($filters);
        $links =  $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('link/admin/list.html.twig', [
            'links' => $links,
            'lastPage' => $paginator->lastPage($links)
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
    ): Response
    {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $link = $form->getData();

            if (null !== $link->getUrl() 
                && (null === $link->getTitle() && null === $link->getDescription() &&null === $link->getImage()) 
                || ($form->has('search') && $form->get('search')->isClicked())) {
                $data = $linkService->getUrlData($link->getUrl());

                if ($data) {
                    $link->setTitle($data['title'])
                        ->setDescription($data['description'])
                        ->setImage($data['image']);
                }
            }
            if ($request->files->get('link')) {
                $pictureFile = $request->files->get('link')['imageFile'];
                if ($pictureFile) {
                    $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();
                    if (!is_dir($this->getParameter('uploads_directory'))) {
                        mkdir($this->getParameter('uploads_directory'));
                    }
                    try {
                        $pictureFile->move(
                            $this->getParameter('uploads_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                    $link->setImage($newFilename);
                }
            }
            $this->entityManager->persist($link);
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_link_edit', ['link' => $link->getId()]);
        }

        return $this->render('link/admin/edit.html.twig', [
            'link' => $link,
            'form' => $form->createView(),
        ]);
    }
}
