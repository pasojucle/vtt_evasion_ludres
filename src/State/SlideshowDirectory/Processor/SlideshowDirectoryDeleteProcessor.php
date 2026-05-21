<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Processor;

use App\Entity\SlideshowDirectory;
use App\Service\ProjectDirService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SlideshowDirectoryDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
    ) {
    }

    public function process(SlideshowDirectory $entity): void
    {
        $id = $entity->getId();
        foreach ($entity->getSlideshowImages() as $image) {
            $this->entityManager->remove($image);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove($this->projectDir->path('slideshow', (string) $id));
    }
}
