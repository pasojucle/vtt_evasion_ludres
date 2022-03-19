<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Service\BikeRideService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class EditBikeRide
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRideService $bikeRideService
    ) {
    }

    public function execute(FormInterface $form): void
    {
        $bikeRide = $form->getData();

        $clusters = $bikeRide->getClusters();

        if ($clusters->isEmpty($bikeRide)) {
            $this->bikeRideService->createClusters($bikeRide);
        }

        $this->entityManager->persist($bikeRide);
        $this->entityManager->flush();
    }
}