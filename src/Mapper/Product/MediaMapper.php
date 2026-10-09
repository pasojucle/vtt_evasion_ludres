<?php

declare(strict_types=1);

namespace App\Mapper\Product;

use App\Dto\View\Product\Tab\MediaView;
use App\Entity\Product;
use App\Service\FileLocation\BikeRideFileLocation;
use App\Service\FileLocation\DefaultFileLocation;
use App\Service\FileLocation\ProductFileLocation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ProductFileLocation $productFileLocation,
        private DefaultFileLocation $defaultFileLocation,
    ) {
    }
    public function mapToView(Product $product): MediaView
    {
        [$directoy, $filename] = $product->getFilename()
            ? [$this->productFileLocation->getBaseDirectoryName(), $product->getFilename()]
            : [$this->defaultFileLocation->getBaseDirectoryName(), 'camera.jpg'];

        return new MediaView(
            alt: $product->getName(),
            filename: $product->getFilename(),
            filePath: $this->urlGenerator->generate('get_data_file', [
                'directory' => $directoy,
                'filename' => $filename,
            ]),
        );
    }
}
