<?php

declare(strict_types=1);

namespace App\Service\Filter;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FilterFieldConfig
{
    public function __construct(
        public string $name,
        public string $type = TextType::class,
        public array $options = [],
        public string $hiddenType = HiddenType::class,
        public array $hiddenOptions = [],
    ) {
    }
}
