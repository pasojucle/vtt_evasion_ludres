<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Product;

class ProductToggleDto
{
    public function __construct(
        public Product $product,
        public ?string $token
    ) {
    }
}
