<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\MessageFilter;
use App\Entity\Enum\LevelType;
use App\Entity\Section;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MessageFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_message_list';
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
                name: 'section',
                type: EntityType::class,
                options:  [
                    'label' => false,
                    'class' => Section::class,
                    'query_builder' => function (EntityRepository $er): QueryBuilder {
                        return $er->createQueryBuilder('se')
                            ->orderBy('se.label', 'ASC');
                    },
                    'placeholder' => 'Toutes les sections',
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
                name: 'label',
                type: TextType::class,
                options:  [
                    'label' => 'Contient',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Saisir un mot clé'
                    ],
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                computedChip: true,
            ),
            new FilterFieldConfig(
                name: 'levelType',
                type: EnumType::class,
                options:  [
                    'label' => 'Type de niveau',
                    'class' => LevelType::class,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),

            AdvancedFilterFlields::createItemsPerPageField(),
            AdvancedFilterFlields::createSortStringField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return MessageFilter::class;
    }
}
