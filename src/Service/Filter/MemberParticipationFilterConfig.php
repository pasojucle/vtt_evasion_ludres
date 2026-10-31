<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\CommonFilterFields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\Filter\RangeChip;
use App\Entity\BikeRideType;
use App\Entity\Member;
use App\Form\HiddenEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class MemberParticipationFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_member_participation_filter';
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
        $period = new RangeChip(
            name: 'period',
            formatLabel: 'Du %s au %s',
            startField: 'startAt',
            endField: 'EndAt',
            required: true,
        );


        return [
            new FilterFieldConfig(
                name: 'member',
                type: HiddenEntityType::class,
                options: [
                    'class' => Member::class,
                ],
            ),
            new FilterFieldConfig(
                name: 'startAt',
                type: DateType::class,
                options: [
                    'label' => 'Date de début',
                    'required' => true,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                rangeChip: $period,
            ),
            
            new FilterFieldConfig(
                name: 'endAt',
                type: DateType::class,
                options: [
                    'label' => 'Date de fin',
                    'required' => true,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                rangeChip: $period,
            ),
            new FilterFieldConfig(
                name: 'type',
                type: EntityType::class,
                options: [
                    'label' => 'Type d\'activité',
                    'class' => BikeRideType::class,
                    'required' => false,
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
            ),
            CommonFilterFields::createItemsPerPageField(),
            CommonFilterFields::createSortDateField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return MemberParticipationFilter::class;
    }
}
