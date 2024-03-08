<?php

declare(strict_types=1);

namespace App\UseCase\Slideshow;

use App\Entity\SlideshowDirectory;
use App\Entity\SlideshowImage;
use App\Repository\SlideshowImageRepository;
use App\Service\ProjectDirService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class DeleteOutOfPeriod
{
    public function __construct(
        private SlideshowImageRepository $slideshowImageRepository,
        private ProjectDirService $projectDir,
        private EntityManagerInterface $entityManager,
    ) {
    }
    public function execute(): array
    {
        $filesystem = new Filesystem();
        $lideshowImages = $this->slideshowImageRepository->findOutOfPeriod();
        $files = [];
        $directories = [];
        /** @var  SlideshowImage $image */
        foreach ($lideshowImages as $image) {
            $files[] = $this->projectDir->path('slideshow', $image->getFilename());
            $directory = $image->getDirectory();
            $directories[$directory->getId()] = $directory;
            $this->entityManager->remove($image);
        }

        $message = 'no slideshow image to delete';
        if (!empty($lideshowImages)) {
            $message = sprintf('%d slideshow images', count($lideshowImages));
            $this->entityManager->flush();
            $filesystem->remove($files);
        }

        /** @var  SlideshowDirectory $directory */
        foreach ($directories as $directory) {
            if ($directory->getSlideshowImages()->isEmpty()) {
                $this->entityManager->remove($directory);
            }
        }
        if (!empty($directories)) {
            $this->entityManager->flush();
        }
        
        return [
            'codeError' => 0,
             'message' => $message,
        ];
    }
}
