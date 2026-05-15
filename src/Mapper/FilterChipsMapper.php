<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\FilterChip;
use App\Service\Filter\FilterConfigInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Contracts\Translation\TranslatorInterface;

class FilterChipsMapper
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function mapToView(AbstractFilter $filter, FilterConfigInterface $filterConfig): array
    {
        $filterSchips = [];
        foreach ($filterConfig->getAdvancedFields() as $field) {
            $name = $field->name;
            $rawValue = $filter->$name;
                    
            if (null !== $rawValue) {
                $value = match ($field->type) {
                    EnumType::class => $rawValue->trans($this->translator),
                    EntityType::class => $rawValue->__toString(),
                    ChoiceType::class => (string) array_flip($field->options['choices'])[$rawValue],
                    default => (string) $rawValue
                };
                if ($field->chipCcomputed) {
                    $value = sprintf('%s: %s', $field->options['label'] ?? $field->name, $value);
                }

                $filterSchips[] = new FilterChip(
                    $name,
                    $value,
                );
            }
        }

        return $filterSchips;
    }
}
