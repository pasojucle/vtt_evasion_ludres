<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Enum\SurveyRestriction;
use App\Dto\Filter\SurveyFilter;
use App\Entity\Enum\SurveyStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class SurveyFilterConfig implements FilterConfigInterface
{
    public function supports(string $route): bool
    {
        return $route === 'admin_surveys';
    }

    public function getDataClass(): ?string
    {
        return SurveyFilter::class;
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
            ])
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
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control']
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
                    'attr' => ['class' => 'form-control']
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
                    'attr' => ['class' => 'form-control']
                ],
            ),
        ];
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        return null;
    }
}
