<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\ActivityParticipantsFilter;
use App\Entity\BikeRide;
use App\Form\Admin\BikeRideAutocompleteField;
use App\Form\HiddenDateType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityParticipantsFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_participation_activity_show';
    }

    public function supports(string $route): bool
    {
        return $route === $this->getRouteName();
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        return null;
    }

    public function isPaginated(): bool
    {
        return false;
    }

    public function getFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'activity',
                type: BikeRideAutocompleteField::class,
                options: [
                    'label' => 'Activité',
                    'class' => BikeRide::class,
                    'autocomplete_url' => 'admin_bike_ride_autocomplete',
                    'attr' => [
                        'data-action' => 'change->filter#updateContent',
                        'data-container-id' => 'activity-participants-drawer'
                    ],
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                allowedFilterNames: ['startAt', 'endAt']
            ),
            new FilterFieldConfig(
                name: 'startAt',
                type: HiddenDateType::class,
                options: []
            ),
            new FilterFieldConfig(
                name: 'endAt',
                type: HiddenDateType::class,
                options: []
            ),
        ];
    }

    public function getAdvancedFields(): array
    {
        return [];
    }

    public function getDataClass(): ?string
    {
        return ActivityParticipantsFilter::class;
    }
}
