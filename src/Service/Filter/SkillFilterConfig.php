<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\SkillFilter;
use App\Entity\Level;
use App\Entity\SkillCategory;
use App\Form\Admin\LevelSchoolAutocompleteField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
    
    public function isPaginated(): bool
    {
        return true;
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
                name: 'content',
                type: TextType::class,
                options: [
                    'label' => 'Contient',
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control'],
                ],
            ),
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
                name: 'showDeleted',
                type: CheckboxType::class,
                options: [
                    'label' => 'Afficher les éléments supprimés',
                    'required' => false,
                    'block_prefix' => 'switch',
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4 flex gap-2 flex-row'],
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
                computedChip: true,
            ),
            AdvancedFilterFlields::createItemsPerPageField(),
            AdvancedFilterFlields::createSortStringField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return SkillFilter::class;
    }
}
