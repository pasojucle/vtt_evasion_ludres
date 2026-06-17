<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\SkillFilter;
use App\Entity\Level;
use App\Entity\SkillCategory;
use App\Form\Admin\LevelSchoolAutocompleteField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SkillFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_skill_list';
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
                name: 'category', 
                type: EntityType::class, 
                options: [
                    'label' => false,
                    'class' => SkillCategory::class,
                    'placeholder' => 'Séléctionner une catégorie',
                    'required' => false,
                    'autocomplete' => true,
                    'attr' => [
                        'data-action' => 'change->filter#submit'
                    ],
                ]
            )
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'level', 
                type: LevelSchoolAutocompleteField::class, 
                options: [
                    'label' => 'Niveau',
                    'class' => Level::class,
                    'required' => false,
                    'autocomplete' => true,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control'],
                ]
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
        return SkillFilter::class;
    }
}
