<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Enum\ActivityPeriod;
use App\Dto\Enum\ActivityRestriction;
use App\Dto\Enum\ActivityVisibility;
use App\Dto\Filter\ActivityFilter;
use App\Form\Filter\EventListener\ActivityFilterSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ActivityFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_bike_ride_list';
    }

    public function supports(string $route): bool
    {
        return $route === $this->getRouteName();
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        return new ActivityFilterSubscriber();
    }
    
    public function isPaginated(): bool
    {
        return true;
    }

    public function getFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'period',
                type: EnumType::class,
                options: [
                    'label' => false,
                    'class' => ActivityPeriod::class,
                    'attr' => [
                        'data-action' => 'change->filter#submit'
                    ],
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
            AdvancedFilterFlields::createActivityTypeField(),
            new FilterFieldConfig(
                name: 'restriction',
                type: EnumType::class,
                options: [
                    'label' => 'Restriction',
                    'class' => ActivityRestriction::class,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
            AdvancedFilterFlields::createShowDeletedField(),
            new FilterFieldConfig(
                name: 'visibility',
                type: EnumType::class,
                options: [
                    'label' => 'Visibilité',
                    'class' => ActivityVisibility::class,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
            AdvancedFilterFlields::createItemsPerPageField(),
            AdvancedFilterFlields::createSortDateField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return ActivityFilter::class;
    }
}
