<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Processor;

use App\Entity\SlideshowImage;
use App\Service\ProjectDirService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SlideshowImageDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
    ) {}

    public function process(SlideshowImage $entity): int
    {
        $directoryId = $entity->getDirectory()->getId();
        $filename = $entity->getFilename();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove($this->projectDir->path('slideshow', (string) $directoryId, $filename));

        return $directoryId;
    }
}