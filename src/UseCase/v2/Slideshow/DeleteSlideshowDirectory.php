<?php

declare(strict_types=1);

namespace App\UseCase\v2\Slideshow;

use App\Entity\SlideshowDirectory;
use App\Repository\Interface\SlideshowDirectoryRepositoryInterface;
use App\Repository\Interface\SlideshowImageRepositoryInterface;
use App\Service\FileLocation\SlideshowFileLocation;
use App\Service\FileService;
use App\Service\OrderByService;
use Symfony\Component\Filesystem\Filesystem;

class DeleteSlideshowDirectory
{
    public function __construct(
        private SlideshowDirectoryRepositoryInterface $slideShowDirectoryRepository,
        private SlideshowImageRepositoryInterface $slideShowImageRepository,
        private OrderByService $orderByService,
        private SlideshowFileLocation $location,
        private FileService $fileService,
    ) {
    }

    public function __invoke(SlideshowDirectory $slideShowDirectory): void
    {
        $id = $slideShowDirectory->getId();
        foreach ($slideShowDirectory->getSlideshowImages() as $image) {
            $this->slideShowImageRepository->remove($image);
        }

        $this->slideShowDirectoryRepository->remove($slideShowDirectory);

        $filesystem = new Filesystem();
        $filesystem->remove($this->fileService->join($this->location->getDirectory(), (string) $id));

        $slideShowDirectorys = $this->slideShowDirectoryRepository->findAllAsc();
        $this->orderByService->resetOrders($slideShowDirectorys);
        
        $this->slideShowDirectoryRepository->remove($slideShowDirectory);
    }
}
