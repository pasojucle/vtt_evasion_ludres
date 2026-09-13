<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\ActivityFramersFilter;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\LevelType;
use App\Entity\Member;
use App\Form\Admin\UserAutocompleteField;
use App\Form\ChoiceProvider\LevelChoiceProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ActivityFramersFilterConfig implements FilterConfigInterface
{
    public function __construct(
        private LevelChoiceProvider $levelChoiceProvider,
    ) {
    }

    public function getRouteName(): string
    {
        return 'admin_bike_ride_framer_list';
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
                name: 'availability',
                type: EnumType::class,
                options: [
                    'label' => 'Disponibilité',
                    'class' => AvailabilityEnum::class,
                    'choices' => [AvailabilityEnum::REGISTERED, AvailabilityEnum::AVAILABLE, AvailabilityEnum::UNAVAILABLE],
                    'attr' => [
                        'data-action' => 'change->filter#updateContent',
                        'data-container-id' => 'activity-framers-drawer'
                    ],
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                    'autocomplete' => true,
                ],
            ),
            new FilterFieldConfig(
                name: 'member',
                type: UserAutocompleteField::class,
                options: [
                    'label' => 'Adhérent',
                    'class' => Member::class,
                    'autocomplete_url' => 'admin_member_autocomplete',
                    'required' => false,
                    'attr' => [
                        'data-action' => 'change->filter#updateContent',
                        'data-container-id' => 'activity-framers-drawer'
                    ],
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                allowedFilterNames: ['levels']
            ),
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'levelType',
                type: EnumType::class,
                options: [
                    'class' => LevelType::class,
                ],
            ),
            new FilterFieldConfig(
                name: 'levels',
                type: ChoiceType::class,
                options: [
                    'multiple' => true,
                    'required' => false,
                    'choices' => $this->levelChoiceProvider->getChoices(),
                ],
            ),
        ];
    }

    public function getDataClass(): ?string
    {
        return ActivityFramersFilter::class;
    }
}
