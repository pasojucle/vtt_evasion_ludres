<?php

declare(strict_types=1);

namespace App\Service\Product;

use App\Entity\Product;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ProductEditService
{
    private UploadService $uploadService;

    private EntityManagerInterface $entityManager;

    public function __construct(UploadService $uploadService, EntityManagerInterface $entityManager)
    {
        $this->uploadService = $uploadService;
        $this->entityManager = $entityManager;
    }

    public function execute(Form &$form, Request $request, bool $persist = false)
    {
        $product = $form->getData();
        $this->setFilename($request, $product);

        if ($persist) {
            $this->entityManager->persist($product);
        }
        
        $this->entityManager->flush();
    }

    public function setFilename(Request $request, Product &$product): void
    {
        if ($request->files->get('product')) {
            $pictureFile = $request->files->get('product')['pictureFile'];
            $newFilename = $this->uploadService->uploadFile($pictureFile, 'products_directory_path');
            if (null !== $newFilename) {
                $product->setFilename($newFilename);
            }
        }
    }
}
