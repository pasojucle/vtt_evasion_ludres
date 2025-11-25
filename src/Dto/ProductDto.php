<?php

declare(strict_types=1);

namespace App\Dto;

class ProductDto
{
    public ?int $id = null;

    public ?string $name = null;

    public ?string $content = null;

    public ?string $price = null;

    public ?string $priceClass = null;

    public ?string $discountPrice = null;

    public ?string $discountTitle = null;

    public ?float $sellingPrice = null;

    public ?string $ref = null;

    public ?string $filename = null;

    public ?string $pathName = null;

    public ?string $pathNameForPdf = null;

    public array $sizes = [];

    public bool $isDisabled = false;
}
