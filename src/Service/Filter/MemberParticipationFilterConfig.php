<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\MemberParticipationFilter;
use App\Entity\Member;
use App\Form\HiddenEntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
        $period = AdvancedFilterFlields::createPeriodRangeChip();

        return [
            new FilterFieldConfig(
                name: 'member',
                type: HiddenEntityType::class,
                options: [
                    'class' => Member::class,
                ],
            ),
            AdvancedFilterFlields::createDateField('startAt', 'Date de début', $period),
            AdvancedFilterFlields::createDateField('endAt', 'Date de fin', $period),
            AdvancedFilterFlields::createActivityTypeField(),
            AdvancedFilterFlields::createItemsPerPageField(),
            AdvancedFilterFlields::createSortDateField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return MemberParticipationFilter::class;
    }
}
