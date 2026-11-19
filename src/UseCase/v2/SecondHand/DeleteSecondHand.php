<?php

declare(strict_types=1);

namespace App\UseCase\v2\SecondHand;

use App\Entity\SecondHand;
use App\Repository\Interface\SecondHandRepositoryInterface;
use App\Service\FileLocation\SecondHandFileLocation;
use Symfony\Component\Filesystem\Filesystem;

class DeleteSecondHand
{
    public function __construct(
        private SecondHandRepositoryInterface $secondHandRepository,
        private SecondHandFileLocation $location,
    ) {
    }

    public function __invoke(SecondHand $secondHand): void
    {
        $images = $secondHand->getImages();
    
        $this->secondHandRepository->remove($secondHand);

        $filesystem = new Filesystem();
        foreach ($images as $image) {
            $filesystem->remove($this->location->getPath($image));
        }

        $this->secondHandRepository->remove($secondHand);
    }
}
