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

    public function execute(FormInterface $form, Request $request, bool $persist = false): BikeRide
    {
        $bikeRide = $form->getData();

        $clusters = $bikeRide->getClusters();

        if ($clusters->isEmpty()) {
            $this->createClusters->execute($bikeRide);
        }

        $file = $request->files->get('bike_ride')['file'] ?? null;
        if ($file) {
            $bikeRide->setFileName($this->uploadService->uploadFile($file));
        }

        $rules = $request->files->get('bike_ride')['rulesFile'] ?? null;
        if ($rules) {
            $bikeRide->setRules($this->uploadService->uploadFile($rules));
        }

        $securityGuidelines = $request->files->get('bike_ride')['securityGuidelinesFile'] ?? null;
        if ($securityGuidelines) {
            $bikeRide->setSecurityGuidelines($this->uploadService->uploadFile($securityGuidelines));
        }

        $rulesThumbnail = $request->files->get('bike_ride')['rulesFileThumbnail'] ?? null;
        if ($rulesThumbnail) {
            $bikeRide->setRulesThumbnail($this->uploadService->uploadFile($rulesThumbnail));
        }

        $securityGuidelinesThumbnail = $request->files->get('bike_ride')['securityGuidelinesFileThumbnail'] ?? null;
        if ($securityGuidelinesThumbnail) {
            $bikeRide->setSecurityGuidelinesThumbnail($this->uploadService->uploadFile($securityGuidelinesThumbnail));
        }

        if ($persist) {
            $this->entityManager->persist($bikeRide);
        }
        
        $this->entityManager->flush();

        return $bikeRide;
    }
}
