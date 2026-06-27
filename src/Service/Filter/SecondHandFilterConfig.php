<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\SecondHandFilter;
use App\Entity\Enum\SecondHandStateEnum;
use App\Entity\Member;
use App\Entity\SecondHandCategory;
use App\Form\Admin\UserAutocompleteField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ])
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
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
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
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'required' => false,
                ],
                allowedFilterNames: ['status'],
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
                    'attr' => ['class' => 'form-control'],
                ],
                chipCcomputed: true,
            ),
            new FilterFieldConfig(
                name: 'sort',
                type: ChoiceType::class,
                options: [
                    'label' => 'Tri',
                    'choices' => [
                        'Date (du plus ancien au plus récent)' => 'ASC',
                        'Date (du plus récent au plus ancien)' => 'DESC',
                    ],
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control'],
                ],
            ),
        ];
    }

    public function getDataClass(): ?string
    {
        return SecondHandFilter::class;
    }
}
