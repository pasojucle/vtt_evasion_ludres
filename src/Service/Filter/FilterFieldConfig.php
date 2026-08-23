<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\RangeChip;
use App\Form\HiddenChoiceType;
use App\Form\HiddenEntityType;
use App\Form\HiddenEnumType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

readonly class FilterFieldConfig
{
    public string $hiddenType;
    public array $hiddenOptions;

    public function __construct(
        public string $name,
        public string $type,
        public array $options,
        public array $allowedFilterNames = [],
        public bool $isSubscriberFlield = false,
        public bool $computedChip = false,
        public ?RangeChip $rangeChip = null,
    ) {
        $dataClass = $this->options['class'] ?? null;
        $multiple = $this->options['multiple'] ?? false;
        $isEntityField = $this->isInstanceOfFormType($this->type, EntityType::class)
            || $this->isInstanceOfFormType($this->type, BaseEntityAutocompleteType::class);
        [$this->hiddenType, $this->hiddenOptions] = match (true) {
            $dataClass && $isEntityField => [HiddenEntityType::class, ['class' => $dataClass]],
            $dataClass && EnumType::class === $this->type => [HiddenEnumType::class, ['class' => $dataClass, 'multiple' => $multiple]],
            ChoiceType::class === $this->type => [HiddenChoiceType::class, ['multiple' => $multiple]],
            default => [HiddenType::class, []]
        };
    }

    private function isInstanceOfFormType(string $childType, string $parentTypeToFind): bool
    {
        if ($childType === $parentTypeToFind) {
            return true;
        }

        if (!class_exists($childType)) {
            return false;
        }

        if (str_starts_with($childType, 'Symfony\\')) {
            return false;
        }

        try {
            $instance = new $childType();
            if (method_exists($instance, 'getParent')) {
                $parent = $instance->getParent();
                
                if ($parent) {
                    return $this->isInstanceOfFormType($parent, $parentTypeToFind);
                }
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}
