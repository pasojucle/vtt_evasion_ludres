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
use App\Service\FileLocation\SlideshowFileLocation;
use App\Service\SlideshowService;
use App\Service\UploadService;
use App\State\SlideshowDirectory\Processor\SlideshowDirectoryDeleteProcessor;
use App\State\SlideshowDirectory\Provider\SlideshowDirectoryDeleteProvider;
use App\State\SlideshowImage\Processor\SlideshowImageDeleteProcessor;
use App\State\SlideshowImage\Provider\SlideshowImageDeleteProvider;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/diaporama', name: 'admin_slideshow_')]
class SlideshowController extends AbstractCrudController
{
    public function __construct(
        private SlideshowDirectoryRepository $slideshowDirectoryRepository,
        private SlideshowImageRepository $slideshowImageRepository,
        private EntityManagerInterface $entityManager,
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
                'parameters' => $this->parameterRepository->findBySectionId('SLIDESHOW'),
            ],
        ]);
    }


    #[Route('/directory/size', name: 'directory_size', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('SLIDESHOW_LIST')]
    public function adminSlideshowDirectorySize(
        SlideshowService $slideshowService
    ): Response {
        return $this->render('slideshow/admin/_directory_size.html.twig', $slideshowService->getSpace());
    }

    #[Route('/directory/add', name: 'directory_add', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_ADD')]
    public function adminSlideshowDirectory_add(
        Request $request
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(SlideshowDirectoryType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $directory = $form->getData();
                $directory->setCeratedAt(new DateTimeImmutable());
                $this->entityManager->persist($directory);
                $this->entityManager->flush();
                return $this->redirect($this->generateUrl('admin_slideshow_list', ['directory' => $directory->getId()]));
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('slideshow/admin/directory_edit.modal.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un répertoire',
            'btn_label' => 'Ajouter',
            'icon' => 'lucide:plus'
        ], $response);
    }

    #[Route('/directory/edit/{directory}', name: 'directory_edit', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_EDIT', 'directory')]
    public function adminSlideshowDirectory_edit(
        Request $request,
        SlideshowDirectory $directory,
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(SlideshowDirectoryType::class, $directory, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->flush();
                return $this->redirect($this->generateUrl('admin_slideshow_list'));
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('slideshow/admin/directory_edit.modal.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier un répertoire',
            'btn_label' => 'Modifier',
            'icon' => 'lucide:edit'
        ], $response);
    }

    #[Route('/directory/delete/{directory}', name: 'directory_delete', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_EDIT', 'directory')]
    public function adminSlideshowDirectoryDelete(
        Request $request,
        SlideshowDirectoryDeleteProcessor $processor,
        SlideshowDirectoryDeleteProvider $provider,
        SlideshowDirectory $directory,
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $directory,
            $provider,
            $processor
        );
    }

    #[Route('/image/delete/{image}', name: 'image_delete', methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_EDIT', 'image')]
    public function adminSlideshowImageDelete(
        Request $request,
        SlideshowImageDeleteProcessor $processor,
        SlideshowImageDeleteProvider $provider,
        SlideshowImage $image,
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $image,
            $provider,
            $processor
        );
    }

    #[Route('/image/upload/{directory}', name: 'image_upload', defaults:['directory' => null], methods: ['GET', 'POST'])]
    #[IsGranted('SLIDESHOW_ADD')]
    public function adminSlideshowImageUpload(
        Request $request,
        UploadService $uploadService,
        SlideshowFileLocation $location,
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
            $uploadedCount = 0;

            /** @var UploadedFile $file */
            foreach ($request->files as $file) {
                $slideShowImage = new SlideshowImage();
                $slideShowImage->setDirectory($directory)
                    ->setCreatedAt(new DateTimeImmutable());
                $filename = $uploadService->uploadFile($file, $slideShowImage);
                if ($filename) {
                    $slideShowImage->setFilename($filename);
                    $absoluteFilePath = $location->getPath($slideShowImage);
                    if ($absoluteFilePath) {
                        $uploadService->resize($absoluteFilePath, UploadService::HD);
                    }

                    $this->entityManager->persist($slideShowImage);
                    $uploadedCount++;
                }
                
                if ($uploadedCount > 0) {
                    $this->entityManager->flush();
                    return new JsonResponse(['errorCode' => 0, 'message' => sprintf('%d image(s) traitée(s)', $uploadedCount)]);
                }
            };
        }

        return new JsonResponse(['errorCode' => 1, 'message' => 'Aucune données valides']);
    }
}
