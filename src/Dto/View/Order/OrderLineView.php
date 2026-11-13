<?php

declare(strict_types=1);

namespace App\Dto\View\Order;

use App\Dto\View\BadgeView;
use App\Dto\View\Product\SellingPriceView;


readonly class OrderLineView
{
    public function __construct(
        public string $productName,
        public string $productPartNumber,
        public string $productUrl,
        public string $productPathName,
        public string $size,
        public int $quantity,
        public SellingPriceView $price,
        public BadgeView $amount,
        public bool $disable,
        public ?BadgeView $status,
    ){}
}
