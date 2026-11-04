<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Enum\RegistrationStatus;
use App\Dto\Filter\RegistrationFilter;
use App\Entity\Member;
use App\Form\Admin\UserAutocompleteField;
use App\Form\ChoiceProvider\LevelChoiceProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class RegistrationFilterConfig implements FilterConfigInterface
{
    public function __construct(
        private LevelChoiceProvider $levelChoiceProvider,
    ) {
    }

    public function getRouteName(): string
    {
        return 'admin_registration_list';
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
        return true;
    }

    public function getFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'status',
                type: EnumType::class,
                options: [
                    'label' => false,
                    'class' => RegistrationStatus::class,
                    'autocomplete' => true,
                    'required' => true,
                    'attr' => [
                        'data-action' => 'change->filter#submit'
                    ],
                ],
            ),
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'member',
                type: UserAutocompleteField::class,
                options: [
                    'label' => 'Adhérent',
                    'class' => Member::class,
                    'autocomplete_url' => 'admin_registration_autocomplete',
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                    'required' => false,
                ],
                allowedFilterNames: ['status'],
            ),
            AdvancedFilterFlields::createLevelsField(
                $this->levelChoiceProvider,
                'change@window->filter#update'
            ),
            AdvancedFilterFlields::createItemsPerPageField(),
            AdvancedFilterFlields::createSortStringField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return RegistrationFilter::class;
    }
}
