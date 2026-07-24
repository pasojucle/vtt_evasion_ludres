<?php

declare(strict_types=1);

namespace App\State\SecondHand\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\SecondHand;
use App\Service\FileLocation\SecondHandFileLocation;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SecondHandDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SecondHandFileLocation $location,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var SecondHand $entity */
        $images = $entity->getImages();
    
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        foreach ($images as $image) {
            $filesystem->remove($this->location->getPath($image));
        }

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'second_hand.flash.success.delete',
        );
    }
}
