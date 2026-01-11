<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SlideshowDirectory;
use App\Entity\SlideshowImage;
use App\Form\Admin\SlideshowDirectoryType;
use App\Form\UploadFileType;
use App\Repository\ParameterRepository;
use App\Repository\SlideshowDirectoryRepository;
use App\Repository\SlideshowImageRepository;
use App\Service\ProjectDirService;
use App\Service\SlideshowService;
use App\Service\UploadService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/diaporama', name: 'admin_slideshow_')]
class SlideshowController extends AbstractController
{
    public function __construct(
        private SlideshowDirectoryRepository $slideshowDirectoryRepository,
        private SlideshowImageRepository $slideshowImageRepository,
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
        private ParameterRepository $parameterRepository,
    ) {
    }

    #[Route('/{directory}', name: 'list', defaults:['directory' => null], methods: ['GET'])]
    #[IsGranted('SLIDESHOW_LIST')]
    public function adminSlideshowList(
        ?SlideshowDirectory $directory,
    ): Response {
        $form = $this->createForm(UploadFileType::class, null, [
            'action' => $this->generateUrl('admin_slideshow_image_upload', ['directory' => $directory?->getId()]),
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
        foreach ($imagesEntities as $imagesEntity) {
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
            'settings' => [
                'parameters' => $this->parameterRepository->findByParameterGroupName('SLIDESHOW'),
            ],
        ]);
    }


    #[Route('/directory/size', name: 'directory_size', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('SLIDESHOW_LIST')]
    public function adminSlideshowDirectorySize(
        SlideshowService $slideshowService
    ): JsonResponse {
        return new JsonResponse(['response' => $slideshowService->getSpace()]);
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
            foreach ($directory->getSlideshowImages() as $image) {
                $this->entityManager->remove($image);
            }

            $filesystem = new Filesystem();
            $filesystem->remove($this->projectDir->path('slideshow', (string) $directory->getId()));

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
        $directory = $image->getDirectory();
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), ),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->projectDir->path('slideshow', (string) $directory->getId(), $image->getFilename()));
            $this->entityManager->remove($image);
            $this->entityManager->flush();
            return $this->redirect($this->generateUrl('admin_slideshow_list', ['directory' => $directory->getId()]));
        }

        return $this->render('slideshow/admin/image_delete.modal.html.twig', [
            'form' => $form->createView(),
            'image' => $image,
        ]);
    }

    #[Route('/image/upload/{directory}', name: 'image_upload', defaults:['directory' => null], methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_ADD')]
    public function adminSlideshowImageUpload(
        Request $request,
        UploadService $uploadService,
        SlideshowService $slideshowService,
        ?SlideshowDirectory $directory
    ): JsonResponse {
        if ($slideshowService->isFull()) {
            return new JsonResponse(['errorCode' => 1, 'message' => 'Espace disque insufisant']);
        }

        $form = $this->createForm(UploadFileType::class, null, [
            'action' => $this->generateUrl($request->attributes->get('_route')),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            foreach ($request->files as $file) {
                $filename = $uploadService->uploadFile($file, 'tmp');
                $uploadService->resize('tmp', $filename, UploadService::HD, $this->projectDir->dir('slideshow', (string) $directory->getId()));
                $slideShowImage = new SlideshowImage();
                $slideShowImage->setFilename($filename)
                    ->setDirectory($directory)
                    ->setCreatedAt(new DateTimeImmutable());
                $this->entityManager->persist($slideShowImage);
                $this->entityManager->flush();
                return new JsonResponse(['errorCode' => 0]);
            };
        }

        return new JsonResponse(['errorCode' => 1, 'message' => 'Auncune données valides']);
    }
}
