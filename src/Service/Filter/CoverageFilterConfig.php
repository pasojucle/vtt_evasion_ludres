<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\CoverageFilter;
use App\Entity\Member;
use App\Form\Admin\UserAutocompleteField;
use App\Form\ChoiceProvider\LevelChoiceProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CoverageFilterConfig implements FilterConfigInterface
{
    public function __construct(
        private LevelChoiceProvider $levelChoiceProvider
    ) {
    }

    public function getRouteName(): string
    {
        return 'admin_coverage_list';
    }

    public function supports(string $route): bool
    {
        return $route === $this->getRouteName();
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        // TODO: Ajoutez le subsciber si besoins
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
                    'autocomplete_url' => 'admin_coverage_autocomplete',
                    'required' => false,
                ],
            ),
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
                        'Nom (de A à Z)' => 'ASC',
                        'Nom (de Z à A)' => 'DESC',
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
        return CoverageFilter::class;
    }
}
