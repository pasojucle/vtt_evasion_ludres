<?php

declare(strict_types=1);

namespace App\Core\Contract\Filter;

use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\RangeChip;
use App\Entity\BikeRideType;
use App\Form\ChoiceProvider\LevelChoiceProvider;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

final class AdvancedFilterFlields
{
    public const array ATTR_ROW = ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'];

    public static function createItemsPerPageField(): FilterFieldConfig
    {
        return new FilterFieldConfig(
            name: 'itemsPerPage',
            type: ChoiceType::class,
            options: [
                'label' => 'Nombre de résultats',
                'choices' => [
                    '15' => 15,
                    '25' => 25,
                    '50' => 50,
                    '100' => 100,
                ],
                'required' => false,
                'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
            ],
            computedChip: true,
        );
    }

    public static function createSortDateField(): FilterFieldConfig
    {
        return new FilterFieldConfig(
            name: 'sort',
            type: ChoiceType::class,
            options: [
                'label' => 'Tri',
                'choices' => [
                    'Date (du plus ancien au plus récent)' => 'ASC',
                    'Date (du plus récent au plus ancien)' => 'DESC',
                ],
                'required' => false,
                'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
            ],
        );
    }

    public static function createSortStringField(): FilterFieldConfig
    {
        return new FilterFieldConfig(
            name: 'sort',
            type: ChoiceType::class,
            options: [
                'label' => 'Tri',
                'choices' => [
                    'Nom (de A à Z)' => 'ASC',
                    'Nom (de Z à A)' => 'DESC',
                ],
                'required' => false,
                'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
            ],
        );
    }

    public static function createLevelsField(LevelChoiceProvider $provider, ?string $dataAction = null): FilterFieldConfig
    {
        return new FilterFieldConfig(
            name: 'levels',
            type: ChoiceType::class,
            options: [
                'label' => 'Niveaux',
                'choices' => $provider->getChoices(),
                'multiple' => true,
                'autocomplete' => true,
                'required' => false,
                'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                'attr' => $dataAction
                    ? [
                        'data-action' => $dataAction,
                    ] : [],
            ],
        );
    }

    public static function createActivityTypeField(): FilterFieldConfig
    {
        return new FilterFieldConfig(
            name: 'type',
            type: EntityType::class,
            options: [
                'label' => 'Type d\'activité',
                'class' => BikeRideType::class,
                'autocomplete' => true,
                'required' => false,
                'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
            ],
        );
    }

    public static function createShowDeletedField(): FilterFieldConfig
    {
        return new FilterFieldConfig(
            name: 'showDeleted',
            type: CheckboxType::class,
            options: [
                'label' => 'Afficher les éléments supprimés',
                'required' => false,
                'block_prefix' => 'switch',
                'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4 flex gap-2 flex-row'],
            ],
            computedChip: true,
        );
    }

    public static function createPeriodRangeChip(): RangeChip
    {
        return new RangeChip(
            name: 'period',
            formatLabel: 'Du %s au %s',
            startField: 'startAt',
            endField: 'EndAt',
            required: true,
        );
    }

    public static function createDateField(string $name, string $label, ?RangeChip $rangeChip): FilterFieldConfig
    {
        return new FilterFieldConfig(
            name: $name,
            type: DateType::class,
            options: [
                'label' => $label,
                'required' => true,
                'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
            ],
            rangeChip: $rangeChip,
        );
    }
}
