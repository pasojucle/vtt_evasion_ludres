<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\SlideshowImage;
use App\Service\ProjectDirService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SlideshowImageDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<SlideshowImage>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $directoryId = $entity->getDirectory()->getId();
        $filename = $entity->getFilename();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove($this->projectDir->path('slideshow', (string) $directoryId, $filename));

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'slideshow.image.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
