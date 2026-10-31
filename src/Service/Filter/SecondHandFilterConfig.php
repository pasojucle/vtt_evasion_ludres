<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\CommonFilterFields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\SecondHandFilter;
use App\Entity\Enum\SecondHandStateEnum;
use App\Entity\Member;
use App\Entity\SecondHandCategory;
use App\Form\Admin\UserAutocompleteField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class SecondHandFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_second_hand_list';
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
                name: 'state',
                type: EnumType::class,
                options:  [
                'label' => false,
                'placeholder' => 'Tous',
                'class' => SecondHandStateEnum::class,
                'attr' => [
                    'data-action' => 'change->filter#submit'
                ],
                'required' => false,
            ]
            )
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'category',
                type: EntityType::class,
                options: [
                    'label' => 'Category',
                    'class' => SecondHandCategory::class,
                    'autocomplete' => true,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                    'required' => false,
                ],
            ),
            new FilterFieldConfig(
                name: 'member',
                type: UserAutocompleteField::class,
                options: [
                    'label' => 'Adhérent',
                    'class' => Member::class,
                    'autocomplete_url' => 'admin_member_autocomplete',
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                    'required' => false,
                ],
                allowedFilterNames: ['status'],
            ),
            CommonFilterFields::createItemsPerPageField(),
            CommonFilterFields::createSortDateField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return SecondHandFilter::class;
    }
}
