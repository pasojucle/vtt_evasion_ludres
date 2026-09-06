<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\UserFilter;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Member;
use App\Form\Admin\UserAutocompleteField;
use App\Form\ChoiceProvider\LevelChoiceProvider;
use App\Form\ChoiceProvider\SeasonChoiceProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class UserFilterConfig implements FilterConfigInterface
{
    public function __construct(
        private LevelChoiceProvider $levelChoiceProvider,
        private SeasonChoiceProvider $seasonChoiceProvider,
    ) {
    }

    public function getRouteName(): string
    {
        return 'admin_user_list';
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
                name: 'member',
                type: UserAutocompleteField::class,
                options: [
                    'label' => false,
                    'class' => Member::class,
                    'autocomplete_url' => 'admin_member_autocomplete',
                    'attr' => [
                        'data-action' => 'change->filter#submit',
                    ],
                    'required' => false,
                ],
                allowedFilterNames: ['levels', 'season', 'isBoardMember', 'permissions']
            )
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'levels',
                type: ChoiceType::class,
                options: [
                    'label' => 'Niveaux',
                    'choices' => $this->levelChoiceProvider->getChoices(),
                    'multiple' => true,
                    'autocomplete' => true,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
            new FilterFieldConfig(
                name: 'season',
                type: ChoiceType::class,
                options: [
                    'label' => 'Saison',
                    'choices' => $this->seasonChoiceProvider->getChoices(),
                    'autocomplete' => true,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),

            new FilterFieldConfig(
                name: 'isBoardMember',
                type: ChoiceType::class,
                options: [
                    'label' => 'Membre du bureau et comité',
                    'choices' => [
                        'Oui' => true,
                        'Non' => false,
                    ],
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                computedChip: true,
            ),
            new FilterFieldConfig(
                name: 'permissions',
                type: EnumType::class,
                options: [
                    'class' => PermissionEnum::class,
                    'multiple' => true,
                    'autocomplete' => true,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
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
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                computedChip: true,
            ),
            new FilterFieldConfig(
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
            ),
        ];
    }

    public function getDataClass(): ?string
    {
        return UserFilter::class;
    }
}
