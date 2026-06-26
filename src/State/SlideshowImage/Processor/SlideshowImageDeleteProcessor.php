<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\SlideshowImage;
use App\Service\ProjectDirService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SlideshowImageDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
    ) {
    }

    /**
     * @implements DialogProcessorInterface<SlideshowImage>
     */
    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        $directoryId = $entity->getDirectory()->getId();
        $filename = $entity->getFilename();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove($this->projectDir->path('slideshow', (string) $directoryId, $filename));

        return new ProcessorResult(
            success: true,
            messageKey: 'slideshow.image.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
