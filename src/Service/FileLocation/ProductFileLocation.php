<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\Product;

class ProductFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {

        return $className === Product::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'product';
    }
}
