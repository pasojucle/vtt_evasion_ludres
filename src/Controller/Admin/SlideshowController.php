<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SlideshowImage;
use App\Entity\SlideshowDirectory;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Admin\SlideshowDirectoryType;
use App\Repository\SlideshowImageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SlideshowDirectoryRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/diaporama', name: 'admin_slideshow_')]
class SlideshowController extends AbstractController
{
    public function __construct(
        private SlideshowDirectoryRepository $slideshowDirectoryRepository,
        private SlideshowImageRepository $slideshowImageRepository,
        private EntityManagerInterface $entityManager,
    )
    {
        
    }

    #[Route('/{directory}', name: 'list', defaults:['directory' => null], methods: ['GET'])]
    #[IsGranted('SLIDESHOW_LIST')]
    public function adminSlideshowList(
        ?SlideshowDirectory $directory,
    ): Response {
        $directories = [];
        $images = [];
        if ($directory) {
            $imagesEntities = $directory->getSlideshowImages();
        } else {
            $directories = $this->slideshowDirectoryRepository->findAllASC();
            $imagesEntities = $this->slideshowImageRepository->findRoot();
        }

        /** @var SlideshowImage $imagesEntity */
        foreach($imagesEntities as $imagesEntity) {
            $images[] = [
                'name' => $imagesEntity->getFilename(),
                'url' => $this->generateUrl('slideshow_image', ['filename' => $imagesEntity->getFilename()]),
            ];
        }

        return $this->render('slideshow/admin/list.html.twig', [
            'directories' => $directories,
            'images' => $images,
        ]);
    }


    #[Route('/directory/add', name: 'directory_add', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_ADD')]
    public function adminSlideshowDirectory_add(
        Request $request
    ): Response {
        $form = $this->createForm(SlideshowDirectoryType::class, null, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), ),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $directory = $form->getData();
            $this->entityManager->persist($directory);
            $this->entityManager->flush();
            return $this->redirect($this->generateUrl('admin_slideshow_list', ['directory' => $directory->getId()]));
        }

        return $this->render('slideshow/admin/edit.modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}