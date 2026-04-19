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

        $files = $request->files->get('bike_ride');
        $file = $files['file'] ?? null;
        if ($file) {
            $bikeRide->setFileName($this->uploadService->uploadFile($file));
        }

        $rules = $files['rulesFile'] ?? null;
        if ($rules) {
            $bikeRide->setRules($this->uploadService->uploadFile($rules));
        }

        $securityGuidelines = $files['securityGuidelinesFile'] ?? null;
        if ($securityGuidelines) {
            $bikeRide->setSecurityGuidelines($this->uploadService->uploadFile($securityGuidelines));
        }

        $rulesThumbnail = $files['rulesFileThumbnail'] ?? null;
        if ($rulesThumbnail) {
            $bikeRide->setRulesThumbnail($this->uploadService->uploadFile($rulesThumbnail));
        }

        $securityGuidelinesThumbnail = $files['securityGuidelinesFileThumbnail'] ?? null;
        if ($securityGuidelinesThumbnail) {
            $bikeRide->setSecurityGuidelinesThumbnail($this->uploadService->uploadFile($securityGuidelinesThumbnail));
        }

        $bikeRideTracks = $bikeRide->getBikeRideTracks();
        if (array_key_exists('bikeRideTracks', $files)) {
            foreach ($files['bikeRideTracks'] as $key => $bikeRideTrackFile) {
                if ($bikeRideTracks->containsKey($key)) {
                    $bikeRideTrack = $bikeRideTracks->get($key);
                    if ($bikeRideTrackFile['file']) {
                        $bikeRideTrack->setFilename($this->uploadService->uploadFile($bikeRideTrackFile['file'], 'bike_ride_track', 'gpx'));
                    }
                    if ($bikeRideTrackFile['thumbnailFile']) {
                        $bikeRideTrack->setThumbnail($this->uploadService->uploadFile($bikeRideTrackFile['thumbnailFile'], 'bike_ride_track'));
                    }
                }
            }
        }

        if ($persist) {
            $this->entityManager->persist($bikeRide);
        }
        
        $this->entityManager->flush();

        return $bikeRide;
    }
}
