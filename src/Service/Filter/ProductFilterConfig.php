<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Enum\PublishStatus;
use App\Dto\Filter\ProductFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_product_list';
    }

    public function supports(string $route): bool
    {
        return $route === $this->getRouteName();
    }

    public function getDataClass(): ?string
    {
        return ProductFilter::class;
    }
    
    public function isPaginated(): bool
    {
        return true;
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
                    'class' => PublishStatus::class,
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
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                computedChip: true,
            ),
            new FilterFieldConfig(
                name: 'showDeleted',
                type: CheckboxType::class,
                options: [
                    'label' => 'Afficher les éléments supprimés',
                    'required' => false,
                    'block_prefix' => 'switch',
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4 flex gap-2 flex-row'],
                ],
                computedChip: true,
            ),
            AdvancedFilterFlields::createItemsPerPageField(),
            AdvancedFilterFlields::createSortStringField(),
        ];
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        return null;
    }
}
