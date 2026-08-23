<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\MemberSkillFilter;
use App\Entity\Level;
use App\Entity\SkillCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MemberSkillFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_member_skill_filter';
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
        return [];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'category',
                type: EntityType::class,
                options: [
                    'label' => 'Categorie',
                    'class' => SkillCategory::class,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
            new FilterFieldConfig(
                name: 'level',
                type: EntityType::class,
                options: [
                    'label' => 'Niveau',
                    'class' => Level::class,
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
                        'Date (du plus ancien au plus récent)' => 'ASC',
                        'Date (du plus récent au plus ancien)' => 'DESC',
                    ],
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
        ];
    }

    public function getDataClass(): ?string
    {
        return MemberSkillFilter::class;
    }
}
