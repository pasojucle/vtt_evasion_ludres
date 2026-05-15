<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Form\HiddenEntityType;
use App\Form\HiddenEnumType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

readonly class FilterFieldConfig
{
    public string $hiddenType;
    public array $hiddenOptions;

    public function __construct(
        public string $name,
        public string $type,
        public array $options,
        public bool $isSubscriberFlield = false,
        public bool $chipCcomputed = false,
    ) {
        $dataClass = $this->options['class'] ?? null;
        [$this->hiddenType, $this->hiddenOptions] = match (true) {
            $dataClass && EntityType::class === $this->type => [HiddenEntityType::class, ['class' => $dataClass]],
            $dataClass && EnumType::class === $this->type => [HiddenEnumType::class, ['class' => $dataClass]],
            default => [HiddenType::class, []]
        };
    }
}
