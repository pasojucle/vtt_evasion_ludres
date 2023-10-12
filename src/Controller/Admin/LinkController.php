<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\Link;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/param/lien', name: 'admin_link')]
class LinkController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LinkRepository $linkRepository,
        private OrderByService $orderByService
    ) {
    }

    #[Route('s/{position}', name: 's', methods: ['GET', 'POST'], defaults:['position' => 1])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        Request $request,
        int $position
    ): Response {
        $query = $this->linkRepository->findLinkQuery($position);
        $links = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('link/admin/list.html.twig', [
            'links' => $links,
            'paginator' => $paginatorDtoTransformer->fromEntities($links, ['position' => (int) $position]),
            'current_position' => $position,
        ]);
    }

    #[Route('/{link}', name: '_edit', methods: ['GET', 'POST'], defaults:['link' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLinkEdit(
        Request $request,
        SluggerInterface $slugger,
        ?Link $link
    ): Response {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $link = $form->getData();

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
            if (-1 === $link->getOrderBy() && null !== $link->getPosition()) {
                $order = $this->linkRepository->findNexOrderByPosition($link->getPosition());
                $link->setOrderBy($order);
            }
            $this->entityManager->persist($link);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_links', [
                'position' => $link->getPosition(),
            ]);
        }

        return $this->render('link/admin/edit.html.twig', [
            'link' => $link,
            'form' => $form->createView(),
        ]);
    }

    #[Route('supprimer/{link}', name: '_delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/ordonner/{link}', name: '_order', methods: ['POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLinkOrder(
        Request $request,
        Link $link
    ): Response {
        $position = $link->getPosition();
        $newOrder = (int) $request->request->get('newOrder');
        $links = $this->linkRepository->findByPosition($position);

        $this->orderByService->setNewOrders($link, $links, $newOrder);

        return new Response();
    }
}
