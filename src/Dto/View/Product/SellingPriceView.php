<?php

declare(strict_types=1);

namespace App\Dto\View\Product;

use App\Core\Contract\View\ComponentViewInterface;
use App\Dto\Enum\ColorVariant;
use App\Dto\View\BadgeView;
use App\Model\SellingPrice;

readonly class SellingPriceView implements ComponentViewInterface
{
    public function __construct(
        public bool $hasDiscount,
        public string $amount,
        public ?BadgeView $reason = null,
        public ?string $basePrice = null,
    ){}

    public static function create(SellingPrice $sellingPrice): self
    {
        if ($sellingPrice->hasDiscount()) {
            return new self(
                true,
                number_format($sellingPrice->amount, 2) . ' €',
                new BadgeView(
                    value: $sellingPrice->reason,
                    variant: ColorVariant::ACCENT,
                ),
                number_format($sellingPrice->basePrice, 2) . ' €',
            );
        }
        
        return new self(
            false,
            number_format($sellingPrice->amount, 2) . ' €',
        );
    }

    public function getName(): string
    {
        return 'selling_price';
    }

    public function getTemplate(): string
    {
        return 'product/selling_price.html.twig';
    }
}