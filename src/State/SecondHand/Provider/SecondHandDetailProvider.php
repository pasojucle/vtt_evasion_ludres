<?php

declare(strict_types=1);

namespace App\State\SecondHand\Provider;

use App\Entity\SecondHand;
use App\Entity\SecondHandImage;
use App\Mapper\SecondHand\SecondHandDetailMapper;
use App\Dto\View\SecondHand\SecondHandDetailView;
use App\Service\FileLocation\SecondHandFileLocation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecondHandDetailProvider
{
    public function __construct(
        private SecondHandDetailMapper $mapper,
        private SecondHandFileLocation $location,
        private UrlGeneratorInterface $urlGenerator,
    ){}

    public function getDetailView(SecondHand $secondHand, string $currentRoute, ?string $listRoute): SecondHandDetailView
    {
        $directory = $this->location->getBaseDirectoryName();

        return $this->mapper->mapToView(
            $secondHand, 
            $this->resolveImages($secondHand, $directory),
            $this->urlGenerator->generate('get_data_file', [
                'directory' => 'default',
                'filename' => 'camera.jpg'
            ]),
            $currentRoute,
            $listRoute,
        );
    }

    /**
     * @return array<int, array{path: string, filename: string}>
     */
    private function resolveImages(SecondHand $secondHand, string $directory): array
    {

        $images = [];
        /** @var SecondHandImage $image */
        foreach ($secondHand->getImages() as $image) {
            $images[$image->getId()] = [
                'path' => $this->urlGenerator->generate('get_data_file', [
                    'directory' => $directory,
                    'filename' => $image->getFilename()
                ]),
                'filename' => $image->getFilename()
            ];
        }

        return $images;
    }
}