<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\SlideshowImage;
use App\Service\ProjectDirService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SlideshowImageDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
    ) {
    }

    /**
     * @implements HtmlProcessorInterface<SlideshowImage>
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $directoryId = $entity->getDirectory()->getId();
        $filename = $entity->getFilename();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove($this->projectDir->path('slideshow', (string) $directoryId, $filename));

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'slideshow.image.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
