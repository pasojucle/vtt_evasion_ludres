<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Product;

interface ProductRepositoryInterface
{
    public function save(Product $product, bool $flush = true): void;

    public function remove(Product $product, bool $flush = true): void;
}
