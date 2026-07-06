<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\SlideshowDirectory;
use App\Service\ProjectDirService;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SlideshowDirectoryDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
    ) {
    }

    /**
     * @implements HtmlProcessorInterface<SlideshowDirectory>
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $id = $entity->getId();
        foreach ($entity->getSlideshowImages() as $image) {
            $this->entityManager->remove($image);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove($this->projectDir->path('slideshow', (string) $id));

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'slideshow.directory.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
