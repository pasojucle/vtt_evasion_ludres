<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Service\UploadService;
use App\UseCase\BikeRide\CreateClusters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditBikeRide
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CreateClusters $createClusters,
        private UploadService $uploadService,
    ) {
    }

    public function execute(FormInterface $form, Request $request): BikeRide
    {
        $bikeRide = $form->getData();

        $clusters = $bikeRide->getClusters();

        if ($clusters->isEmpty($bikeRide)) {
            $this->createClusters->execute($bikeRide);
        }

        if ($request->files->get('bike_ride')) {
            $file = $request->files->get('bike_ride')['file'];
            $bikeRide->setFileName($this->uploadService->uploadFile($file));
        }

        $this->entityManager->persist($bikeRide);
        $this->entityManager->flush();

        return $bikeRide;
    }
}
