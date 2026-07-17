<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\LevelFilter;
use App\Entity\Enum\LevelType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class LevelFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_level_list';
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
                name: 'type',
                type: EnumType::class,
                options: [
                    'label' => false,
                    'class' => LevelType::class,
                    'required' => false,
                    'attr' => [
                        'data-action' => 'change->filter#submit'
                    ],
                ],
            )
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
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
        return LevelFilter::class;
    }
}
