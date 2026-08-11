<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\MemberParticipationFilter;
use App\Entity\BikeRideType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class MemberParticipationFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_member_participation_list';
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
                type: EntityType::class,
                options: [
                    'label' => 'Type d\'activité',
                    'class' => BikeRideType::class,
                    'required' => false,
                    'attr' => [
                        'data-action' => 'change->filter#submit'
                    ],                ],
            ),
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'startAt',
                type: DateType::class,
                options: [
                    'label' => 'Type d\'activité',
                    'class' => BikeRideType::class,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
            
            new FilterFieldConfig(
                name: 'startAt',
                type: DateType::class,
                options: [
                    'label' => 'Type d\'activité',
                    'class' => BikeRideType::class,
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
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
        ];
    }

    public function getDataClass(): ?string
    {
        return MemberParticipationFilter::class;
    }
}
