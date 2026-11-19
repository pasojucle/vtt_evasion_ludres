<?php

declare(strict_types=1);

namespace App\UseCase\v2\Slideshow;

use App\Entity\SlideshowImage;
use App\Repository\Interface\SlideshowImageRepositoryInterface;
use App\Service\FileLocation\SlideshowFileLocation;
use App\Service\FileService;
use Symfony\Component\Filesystem\Filesystem;

class DeleteSlideshowImage
{
    public function __construct(
        private SlideshowImageRepositoryInterface $slideShowImageRepository,
        private SlideshowFileLocation $location,
        private FileService $fileService,
    ) {
    }

    public function __invoke(SlideshowImage $sideShowImage): void
    {
        $id = $sideShowImage->getId();

        $filename = $sideShowImage->getFilename();
        $this->slideShowImageRepository->remove($sideShowImage);

        $filesystem = new Filesystem();
        $filesystem->remove($this->fileService->join($this->location->getDirectory(), (string) $id, $filename));
    }
}
