<?php

declare(strict_types=1);

namespace App\Dto\View\SecondHand;

use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use Symfony\Component\Form\Button;

class SecondHandDetailView
{
    /**
     * @param array<int, array{path: string, filename: string}> $images
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $content,
        public string $price,
        public BadgeView $categoryBadge,
        public string $categoryName,
        public string $createdAt,
        public BadgeView $state,
        public array $images,
        public string $mainImagePath,
        public string $sellerName,
        public string $sellerEmail,
        public string $sellerPhone,
        public ButtonView $buttonEdit,
        public ButtonView $buttonDelete,
        public ?ButtonView $buttonValidate = null,
    ) {
    }
}
