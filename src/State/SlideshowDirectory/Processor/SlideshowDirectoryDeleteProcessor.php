<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\SlideshowDirectory;
use App\Service\ProjectDirService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SlideshowDirectoryDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectDirService $projectDir,
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<SlideshowDirectory>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $id = $entity->getId();
        foreach ($entity->getSlideshowImages() as $image) {
            $this->entityManager->remove($image);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove($this->projectDir->path('slideshow', (string) $id));

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'slideshow.directory.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
