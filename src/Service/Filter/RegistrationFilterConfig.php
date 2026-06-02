<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Enum\RegistrationStatus;
use App\Dto\Filter\RegistrationFilter;
use App\Entity\Member;
use App\Form\Admin\UserAutocompleteField;
use App\Form\ChoiceProvider\LevelChoiceProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationFilterConfig implements FilterConfigInterface
{
    public function __construct(
        private LevelChoiceProvider $levelChoiceProvider,
        private UrlGeneratorInterface $urlGenerator,
    )
    { 

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
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'required' => false,
                ],
                allowedFilterNames: ['status'],
            ),
            new FilterFieldConfig(
                name: 'levels',
                type: ChoiceType::class,
                options: [
                    'label' => 'Niveaux',
                    'choices' => $this->levelChoiceProvider->getChoices(),
                    'multiple' => true,
                    'autocomplete' => true,
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => [
                        'data-action' => 'change@window->filter#update',
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
                    'attr' => [
                        'class' => 'form-control',
                    ]
                ],
                chipCcomputed: true,
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
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control']
                ],
            ),
        ];
    }

    public function getDataClass(): ?string
    {
        return RegistrationFilter::class;
    }
}
