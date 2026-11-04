<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\MemberSkillFilter;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\Level;
use App\Entity\SkillCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

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
    
    public function isPaginated(): bool
    {
        return true;
    }

    public function getFields(): array
    {
        return [];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'evaluation',
                type: EnumType::class,
                options: [
                    'label' => 'Évaluation',
                    'class' => EvaluationEnum::class,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
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
            AdvancedFilterFlields::createItemsPerPageField(),
            AdvancedFilterFlields::createSortDateField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return MemberSkillFilter::class;
    }
}
