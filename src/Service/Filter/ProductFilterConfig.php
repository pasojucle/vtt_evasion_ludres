<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Enum\ProductState;
use App\Dto\Filter\ProductFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductFilterConfig implements FilterConfigInterface
{
    public function supports(string $route): bool
    {
        return $route === 'admin_products';
    }

    public function getDataClass(): ?string
    {
        return ProductFilter::class;
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
                    'class' => ProductState::class,
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
                name: 'partNumber',
                type: TextType::class,
                options: [
                    'label' => 'Référence',
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control'],
                ],
                chipCcomputed: true,
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

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        return null;
    }
}
