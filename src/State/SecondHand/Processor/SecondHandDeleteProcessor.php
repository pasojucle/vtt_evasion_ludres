<?php

declare(strict_types=1);

namespace App\State\SecondHand\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\SecondHand;
use App\Service\FileLocation\SecondHandFileLocation;
use App\Service\FilterDecoderService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SecondHandDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilterDecoderService $filterDecoder,
        private SecondHandFileLocation $location,
    ) {
    }

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        /** @var SecondHand $entity */
        $images = $entity->getImages();
    
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $filesystem = new Filesystem();
        foreach($images as $image) {
            $filesystem->remove($this->location->getPath($image));
        }

        return new ProcessorResult(
            success: true,
            targetRoute: 'admin_second_hand_list',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: 'second_hand.flash.success.received',
        );
    }
}
