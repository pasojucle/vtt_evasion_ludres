<?php

namespace App\Service\Product;

use App\Entity\Product;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
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
        $this->entityManager =$entityManager;
    }
    public function execute(Form &$form, ?Product $originalProduct, Request $request)
    {
        $product = $form->getData();
        $this->setFilename($request, $product);
        $this->setSizes($originalProduct, $product);

        if (null === $product->getFilename()) {
            $form->addError(new FormError('Veuiller séléectionner une photo'));
        }

        if ($form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
    }

    Public function setFilename(Request $request, Product &$product): void
    {
        if ($request->files->get('product')) {
            $pictureFile = $request->files->get('product')['pictureFile'];
            $newFilename = $this->uploadService->uploadFile($pictureFile, 'products_directory_path');
            if (null !== $newFilename) {
                $product->setFilename($newFilename);
            }
        }
    }

    Public function setSizes(?Product $originalProduct, Product $product): void
    {
        $sizesIds = [];
        if (null === $product) {
            if (!$product->getSizes()->isEmpty()) {
                foreach($product->getSizes() as $size) {
                    $sizesIds[] = $size->getId();
                }
            }

            if (!$originalProduct->getSizes()->isEmpty()) {
                foreach($originalProduct->getSizes() as $size) {
                    if (!in_array($size->getId(), $sizesIds)) {
                        $this->entityManager->remove($size);
                    }
                }
            }
        }

    }
}