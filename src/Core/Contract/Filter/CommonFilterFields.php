<?php

declare(strict_types=1);

namespace App\Core\Contract\Filter;

use App\Core\Filter\FilterFieldConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class CommonFilterFields
{
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
}
