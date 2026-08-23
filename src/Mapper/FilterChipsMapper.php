<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Filter\AbstractFilter;
use App\Dto\View\FilterChipView;
use App\Service\Filter\FilterFieldConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FilterChipsMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mapToView(AbstractFilter $filter, string $routeName, array $fields, string $turboFrame = '_top'): array
    {
        $filterSchips = [];
        $rangesChips = [];
        $queries = $filter->toQueryParams();
        $preserdNullAttributes = $filter->getPreservedNullAttributes();
        foreach ($fields as $field) {
            $name = $field->name;
            $rawValue = $filter->$name;
            $isPreserdeNull = in_array($name, $preserdNullAttributes);
            if (null === $rawValue) {
                continue;
            }
            if (is_array($rawValue)) {
                foreach ($rawValue as $key => $rawValueItem) {
                    $this->addChip(
                        $field,
                        $name,
                        $rawValueItem,
                        $filterSchips,
                        $routeName,
                        $queries,
                        $isPreserdeNull,
                        $turboFrame,
                        $key
                    );
                }
                continue;
            }

            if ($field->rangeChip) {
                $rangeName = $field->rangeChip->name;
                if (!array_key_exists($rangeName, $rangesChips)) {
                    $field->rangeChip->queries = $queries;
                    $rangesChips[$rangeName] = $field->rangeChip;
                }
                $rangeChip = $rangesChips[$rangeName];
                $rangeChip->setValue($name, $this->formatRawValue($field, $rawValue));
                $rangeChip->queries = $this->getRemoveFromQueries($rangeChip->queries, $name, $isPreserdeNull);
                if ($rangeChip->isComplete()) {
                    $filterSchips[] = new FilterChipView(
                        sprintf($rangeChip->formatLabel, $rangeChip->startValue, $rangeChip->endValue),
                        $rangeChip->required ? null : $this->urlGenerator->generate($routeName, $rangeChip->queries),
                        $turboFrame,
                    );
                }
                continue;
            }

            $this->addChip(
                $field,
                $name,
                $rawValue,
                $filterSchips,
                $routeName,
                $queries,
                $isPreserdeNull,
                $turboFrame,
            );
        }

        return $filterSchips;
    }

    private function addChip(
        FilterFieldConfig $field,
        string $name,
        mixed $rawValue,
        array &$filterSchips,
        string $routeName,
        array $queries,
        bool $isPreserdNull,
        string $turboFrame,
        ?int $key = null,
    ): void {
        $label = $this->formatRawValue($field, $rawValue);

        if ($field->computedChip) {
            $label = (CheckboxType::class === $field->type)
                ? sprintf('%s', $field->options['label'] ?? $name)
                : sprintf('%s: %s', $field->options['label'] ?? $name, $label);
        }
        
        $cleanQueries = $this->getRemoveFromQueries($queries, $name, $isPreserdNull, $key);
        $filterSchips[] = new FilterChipView(
            $label,
            $field->options['require'] ?? false ? null : $this->urlGenerator->generate($routeName, $cleanQueries),
            $turboFrame,
        );
    }

    private function formatRawValue(FilterFieldConfig $field, mixed $rawValue): string
    {
        return match ($field->type) {
            EnumType::class => $rawValue->trans($this->translator),
            EntityType::class => $rawValue->__toString(),
            ChoiceType::class => $this->resolveChoiceLabel($field->options['choices'], $rawValue),
            DateType::class => $rawValue?->format('d/m/Y'),
            default => (string) $rawValue
        };
    }

    private function resolveChoiceLabel(array $choices, mixed $searchedValue): string
    {
        foreach ($choices as $label => $choice) {
            if (is_array($choice)) {
                $nestedLabel = array_search($searchedValue, $choice);
                if (false !== $nestedLabel) {
                    return (string) $nestedLabel;
                }
            } elseif ($choice === $searchedValue) {
                return (string) $label;
            }
        }

        return (string) $searchedValue;
    }

    private function getRemoveFromQueries(array $queries, string $name, bool $isPreservedNull, ?int $key = null): array
    {
        if (null !== $key) {
            if ($isPreservedNull) {
                $queries[$name][$key] = '';
            } else {
                unset($queries[$name][$key]);
            }
        } else {
            if ($isPreservedNull) {
                $queries[$name] = '';
            } else {
                unset($queries[$name]);
            }
        }

        return $queries;
    }
}
