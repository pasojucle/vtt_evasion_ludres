<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\BikeRide;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ActivityDto
{
    /**
     * @param ?UploadedFile[] $uploadFiles
     */
    public function __construct(
        public BikeRide $activity,
        public ?array $uploadFiles
    ) {
    }
}
