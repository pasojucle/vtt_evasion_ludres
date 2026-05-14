<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Enum\ActivityPeriod;
use App\Dto\Enum\ActivityRestriction;
use App\Dto\Enum\ActivityVisibility;
use App\Dto\Filter\ActivityFilter;
use App\Entity\BikeRideType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ActivityFilterConfig implements FilterConfigInterface
{
    public function supports(string $route): bool
    {
        return $route === 'admin_bike_rides';
    }

    public function getFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'period',
                type: EnumType::class,
                options: [
                    'class' => ActivityPeriod::class,
                ]
            ),
            new FilterFieldConfig(
                name: 'month',
                type: ChoiceType::class,
                options: [],
            ),
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'type',
                type: EntityType::class,
                options: [
                    'label' => 'Type d\'activité',
                    'class' => BikeRideType::class,
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control']
                ],
            ),
            new FilterFieldConfig(
                name: 'restriction',
                type: EnumType::class,
                options: [
                    'label' => 'Restriction',
                    'class' => ActivityRestriction::class,
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control']
                ],
            ),
            new FilterFieldConfig(
                name: 'visibility',
                type: EnumType::class,
                options: [
                    'label' => 'Visibilité',
                    'class' => ActivityVisibility::class,
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            ),
            new FilterFieldConfig(
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
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control']
                ],
            ),
            new FilterFieldConfig(
                name: 'sort',
                type: ChoiceType::class,
                options: [
                    'label' => 'Tri',
                    'choices' => [
                        'Par date croissante' => 'ASC',
                        'Par date décroissante' => 'DESC',
                    ],
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control']
                ],
            ),
        ];
    }

    public function getDataClass(): ?string
    {
        return ActivityFilter::class;
    }
}
