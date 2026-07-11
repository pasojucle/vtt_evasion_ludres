<?php

declare(strict_types=1);

namespace App\State\SecondHand\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\SecondHand;
use App\Service\FileLocation\SecondHandFileLocation;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SecondHandDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SecondHandFileLocation $location,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var SecondHand $entity */
        $images = $entity->getImages();
    
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        foreach ($images as $image) {
            $filesystem->remove($this->location->getPath($image));
        }

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'second_hand.flash.success.delete',
        );
    }
}
