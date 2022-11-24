<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\Level;
use App\Form\Admin\LevelType;
use App\Repository\LevelRepository;
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
        private LevelRepository $levelRepository
    ) {
    }

    public function execute(FormInterface $form, Request $request): void
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

        $this->setLevelsAndLevelTypes($bikeRide);

        $this->entityManager->persist($bikeRide);
        $this->entityManager->flush();
    }

    private function setLevelsAndLevelTypes(BikeRide $bikeRide): void
    {
        $levelTypes = [];
        $bikeRide->clearLevels();
        foreach ($bikeRide->getLevelFilter() as $filter) {
            match ($filter) {
                Level::TYPE_ALL_MEMBER => $levelTypes[] = Level::TYPE_SCHOOL_MEMBER,
                Level::TYPE_ALL_FRAME => $levelTypes[] = Level::TYPE_FRAME,
                default => $this->addLevel($filter, $bikeRide)
            };
        }
        
        $bikeRide->setLevelTypes($levelTypes);
    }

    private function addLevel(int $levelId, BikeRide $bikeRide): void
    {
        $level = $this->levelRepository->find($levelId);
        if ($level) {
            $bikeRide->addLevel($level);
        }
    }
}
