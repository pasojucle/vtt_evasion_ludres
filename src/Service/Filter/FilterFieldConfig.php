<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Form\HiddenChoiceType;
use App\Form\HiddenEntityType;
use App\Form\HiddenEnumType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $multiple = $this->options['multiple'] ?? false;
        [$this->hiddenType, $this->hiddenOptions] = match (true) {
            $dataClass && EntityType::class === $this->type => [HiddenEntityType::class, ['class' => $dataClass]],
            $dataClass && EnumType::class === $this->type => [HiddenEnumType::class, ['class' => $dataClass, 'multiple' => $multiple]],
            ChoiceType::class === $this->type => [HiddenChoiceType::class, ['multiple' => $multiple]], 
            default => [HiddenType::class, []]
        };
    }
}
