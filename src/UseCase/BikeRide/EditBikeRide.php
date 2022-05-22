<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Service\BikeRideService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditBikeRide
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRideService $bikeRideService,
        private UploadService $uploadService
    ) {
    }

    public function execute(FormInterface $form, Request $request): void
    {
        $bikeRide = $form->getData();

        $clusters = $bikeRide->getClusters();

        if ($clusters->isEmpty($bikeRide)) {
            $this->bikeRideService->createClusters($bikeRide);
        }
        dump($request->files->all());
        if ($request->files->get('bike_ride')) {
            $file = $request->files->get('bike_ride')['file'];
            dump($file);
            $bikeRide->setFileName($this->uploadService->uploadFile($file));
        }

        $this->entityManager->persist($bikeRide);
        $this->entityManager->flush();
    }
}
