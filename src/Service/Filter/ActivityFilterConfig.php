<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Enum\ActivityPeriod;
use App\Dto\Filter\ActivityFilter;
use App\Form\HiddenEnumType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

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
                hiddenType: HiddenEnumType::class,
                hiddenOptions: ['class' => ActivityPeriod::class]
            ),
            new FilterFieldConfig(
                name: 'month',
            ),
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            'sort' => new FilterFieldConfig(
                name: 'sort',
                type: ChoiceType::class,
                options: [
                    'label' => 'Tri',
                    'choices' => [
                        'Par date croissante' => 'ASC',
                        'Par date décroissante' => 'DESC',
                    ],
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-first:border-border not-first:border-b'],
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
