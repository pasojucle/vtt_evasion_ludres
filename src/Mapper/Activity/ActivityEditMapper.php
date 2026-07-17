<?php

declare(strict_types=1);

namespace App\Mapper\Activity;

use App\Dto\View\Activity\ActivityDto;
use App\Entity\BikeRide;
use App\Service\FileLocation\BikeRideFileLocation;
use App\Service\FileLocation\DefaultFileLocation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityEditMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private BikeRideFileLocation $bikeRideFileLocation,
        private DefaultFileLocation $defaultFileLocation,
    ) {
    }
    public function mapToView(BikeRide $entity): ActivityDto
    {
        [$directoy, $filename] = $entity->getFilename()
            ? [$this->bikeRideFileLocation->getBaseDirectoryName(), $entity->getFilename()]
            : [$this->defaultFileLocation->getBaseDirectoryName(), 'camera.jpg'];

        return new ActivityDto(
            id: $entity->getId(),
            title: $entity->getTitle(),
            filename: $entity->getFilename(),
            filePath: $this->urlGenerator->generate('get_data_file', [
                'directory' => $directoy,
                'filename' => $filename,
            ]),
            isPublic: $entity->getBikeRideType()?->isPublic() ?? false,
        );
    }
}
