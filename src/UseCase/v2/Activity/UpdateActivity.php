<?php

declare(strict_types=1);

namespace App\UseCase\v2\Activity;

use App\Entity\BikeRide;
use App\Service\UploadService;
use App\UseCase\v2\Activity\CreateClusters;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateActivity
{
    public function __construct(
        private CreateClusters $createClusters,
        private UploadService $uploadService,
    ) {
    }

    /**
     * @param UploadedFile[] $files
     */
    public function execute(BikeRide $bikeRide, array $files): BikeRide
    {
        $clusters = $bikeRide->getClusters();

        if ($clusters->isEmpty()) {
            $this->createClusters->execute($bikeRide);
        }

        $this->uploadMedias($files, $bikeRide);
        $this->uploadParcours($files, $bikeRide);

        return $bikeRide;
    }

    /** @param UploadedFile[] $files */
    private function uploadMedias(array $files, BikeRide $bikeRide): void
    {
        $file = $files['file'] ?? null;
        if ($file) {
            $bikeRide->setFileName($this->uploadService->uploadFile($file, $bikeRide));
        }

        $rules = $files['rulesFile'] ?? null;
        if ($rules) {
            $bikeRide->setRules($this->uploadService->uploadFile($rules, $bikeRide));
        }

        $securityGuidelines = $files['securityGuidelinesFile'] ?? null;
        if ($securityGuidelines) {
            $bikeRide->setSecurityGuidelines($this->uploadService->uploadFile($securityGuidelines, $bikeRide));
        }

        $rulesThumbnail = $files['rulesFileThumbnail'] ?? null;
        if ($rulesThumbnail) {
            $bikeRide->setRulesThumbnail($this->uploadService->uploadFile($rulesThumbnail, $bikeRide));
        }

        $securityGuidelinesThumbnail = $files['securityGuidelinesFileThumbnail'] ?? null;
        if ($securityGuidelinesThumbnail) {
            $bikeRide->setSecurityGuidelinesThumbnail($this->uploadService->uploadFile($securityGuidelinesThumbnail, $bikeRide));
        }
    }

    private function uploadParcours(array $files, BikeRide $bikeRide): void
    {
        $bikeRideTracks = $bikeRide->getBikeRideTracks();
        if (array_key_exists('bikeRideTracks', $files)) {
            foreach ($files['bikeRideTracks'] as $key => $bikeRideTrackFile) {
                if ($bikeRideTracks->containsKey($key)) {
                    $bikeRideTrack = $bikeRideTracks->get($key);
                    if ($bikeRideTrackFile['file']) {
                        $bikeRideTrack->setFilename($this->uploadService->uploadFile($bikeRideTrackFile['file'], $bikeRideTrack, 'gpx'));
                    }
                    if ($bikeRideTrackFile['thumbnailFile']) {
                        $bikeRideTrack->setThumbnail($this->uploadService->uploadFile($bikeRideTrackFile['thumbnailFile'], $bikeRideTrack));
                    }
                }
            }
        }
    }
}
