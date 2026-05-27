<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\FilterChip;
use App\Service\Filter\FilterConfigInterface;
use App\Service\Filter\FilterFieldConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

    public function mapToView(AbstractFilter $filter, FilterConfigInterface $filterConfig): array
    {
        $filterSchips = [];
        $queries = $filter->toQueryParams();
        $routeName = $filterConfig->getRouteName();
        foreach ($filterConfig->getAdvancedFields() as $field) {
            $name = $field->name;
            $rawValue = $filter->$name;
            if (null === $rawValue) {
                continue;
            }
            if (is_array($rawValue)) {
                foreach($rawValue as $key => $rawValueItem) {
                    $this->addChip($field, $name, $rawValueItem, $filterSchips, $routeName, $queries, $key);
                }
                continue;
            }

            $this->addChip($field, $name, $rawValue, $filterSchips, $routeName, $queries);
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
        ?int $key = null,
    ): void
    {
        $label = match ($field->type) {
            EnumType::class => $rawValue->trans($this->translator),
            EntityType::class => $rawValue->__toString(),
            ChoiceType::class => $this->resolveChoiceLabel($field->options['choices'], $rawValue),
            default => (string) $rawValue
        };

        if ($field->chipCcomputed) {
            $label = sprintf('%s: %s', $field->options['label'] ?? $name, $label);
        }

        $filterSchips[] = new FilterChip(
            $label,
            $this->getRemoveUrl($routeName, $queries, $name, $key),
        );
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

    private function getRemoveUrl(string $routeName, array $queries, string $name, ?int $key): string
    {
        if (null !== $key) {
            unset($queries[$name][$key]);
        } else {
            unset($queries[$name]);
        }

        return $this->urlGenerator->generate($routeName, $queries);
    }
}
