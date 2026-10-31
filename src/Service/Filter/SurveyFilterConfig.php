<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\CommonFilterFields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\SurveyFilter;
use App\Entity\Enum\SurveyRestriction;
use App\Entity\Enum\SurveyStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class SurveyFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_survey_list';
    }

    public function supports(string $route): bool
    {
        return $route === $this->getRouteName();
    }

    public function getDataClass(): ?string
    {
        return SurveyFilter::class;
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
                options:  [
                'label' => false,
                'placeholder' => 'Tous',
                'class' => SurveyStatusEnum::class,
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
                name: 'restriction',
                type: EnumType::class,
                options: [
                    'label' => 'Restriction',
                    'class' => SurveyRestriction::class,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
            CommonFilterFields::createItemsPerPageField(),
            CommonFilterFields::createSortDateField(),
        ];
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        return null;
    }
}
