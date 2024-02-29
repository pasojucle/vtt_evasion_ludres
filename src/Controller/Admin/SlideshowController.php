<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use DateTimeImmutable;
use App\Form\UploadFileType;
use App\Entity\SlideshowImage;
use App\Entity\SlideshowDirectory;
use App\Service\ProjectDirService;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Admin\SlideshowDirectoryType;
use App\Repository\SlideshowImageRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SlideshowDirectoryRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/diaporama', name: 'admin_slideshow_')]
class SlideshowController extends AbstractController
{
    public function __construct(
        private SlideshowDirectoryRepository $slideshowDirectoryRepository,
        private SlideshowImageRepository $slideshowImageRepository,
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
    )
    {
        
    }

    #[Route('/{directory}', name: 'list', defaults:['directory' => null], methods: ['GET'])]
    #[IsGranted('SLIDESHOW_LIST')]
    public function adminSlideshowList(
        ?SlideshowDirectory $directory,
    ): Response {
        $form = $this->createForm(UploadFileType::class, null, [
            'action' => $this->generateUrl('admin_slideshow_image_upload'),
        ]);
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
                'id' => $imagesEntity->getId(),
                'name' => $imagesEntity->getFilename(),
                'url' => $this->generateUrl('slideshow_image', ['filename' => $imagesEntity->getFilename()]),
            ];
        }

        return $this->render('slideshow/admin/list.html.twig', [
            'directories' => $directories,
            'directory' => $directory,
            'images' => $images,
            'form' => $form->createView(),
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
            $directory->setCeratedAt(new DateTimeImmutable());
            $this->entityManager->persist($directory);
            $this->entityManager->flush();
            return $this->redirect($this->generateUrl('admin_slideshow_list', ['directory' => $directory->getId()]));
        }

        return $this->render('slideshow/admin/add.modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/directory/edit/{directory}', name: 'directory_edit', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_EDIT', 'directory')]
    public function adminSlideshowDirectory_edit(
        Request $request,
        SlideshowDirectory $directory,
    ): Response {
        $form = $this->createForm(SlideshowDirectoryType::class, $directory, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), ),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirect($this->generateUrl('admin_slideshow_list'));
        }

        return $this->render('slideshow/admin/directory_edit.modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/directory/delete/{directory}', name: 'directory_delete', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_EDIT', 'directory')]
    public function adminSlideshowDirectoryDelete(
        Request $request,
        SlideshowDirectory $directory,
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), ),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $images = [];
            foreach($directory->getSlideshowImages() as $image) {
                $images[] = $this->projectDir->path('public', 'images', $image->getFilename());
            }
            if (!empty($images)) {
                $filesystem = new Filesystem;
                $filesystem->remove($images);
            }

            $this->entityManager->remove($directory);
            $this->entityManager->flush();
            return $this->redirect($this->generateUrl('admin_slideshow_list'));
        }

        return $this->render('slideshow/admin/directory_delete.modal.html.twig', [
            'form' => $form->createView(),
            'directory' => $directory,
            'has_images' => !$directory->getSlideshowImages()->isEmpty(),
        ]);
    }

    #[Route('/image/delete/{image}', name: 'image_delete', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_EDIT', 'image')]
    public function adminSlideshowImageDelete(
        Request $request,
        SlideshowImage $image,
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), ),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filesystem = new Filesystem;
            $filesystem->remove($this->projectDir->path('public', 'images', $image->getFilename()));
            $this->entityManager->remove($image);
            $this->entityManager->flush();
            return $this->redirect($this->generateUrl('admin_slideshow_list'));
        }

        return $this->render('slideshow/admin/image_delete.modal.html.twig', [
            'form' => $form->createView(),
            'image' => $image,
        ]);
    }

    #[Route('/image/upload', name: 'image_upload', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_ADD')]
    public function adminSlideshowImageUpload(
        Request $request
    ): Response {
        $form = $this->createForm(UploadFileType::class, null, [
            'action' => $this->generateUrl($request->attributes->get('_route')),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            dump($form->get('uploadFile')->getF);

        }

        return new Response();
    }
}