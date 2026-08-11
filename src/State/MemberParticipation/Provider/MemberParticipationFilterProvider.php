<?php

declare(strict_types=1);

namespace App\State\MemberParticipation\Provider;

use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\View\MemberParticipation\MemberParticipationFilterView;
use App\Dto\View\SheetView;
use App\Mapper\MemberParticipation\MemberParticipationFilterMapper;
use App\State\FilterHydratorTrait;
use App\State\Interface\ListFilteredProviderInterface;
use App\State\Interface\TurboStreamProviderInterface;


/**
 * @implements TurboStreamProviderInterface<MemberParticipationFilter>
 */
class MemberParticipationFilterProvider implements TurboStreamProviderInterface, ListFilteredProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private MemberParticipationFilterMapper $memberParticipationFilterMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le filtre de recherche',
            action: 'Modifier'
        );
        
    }

    public function getFormOptions(object $entity): array
    {   

        return [];
    }

    public function getStreamView(object $entity): MemberParticipationFilterView
    {
        return $this->memberParticipationFilterMapper->mapToView($entity);
    }
}
