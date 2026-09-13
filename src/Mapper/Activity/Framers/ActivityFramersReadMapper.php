<?php

declare(strict_types=1);

namespace App\Mapper\Activity\Framers;

use App\Dto\View\EmptyView;
use App\Dto\View\ListDrawerView;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Member;

class ActivityFramersReadMapper
{
    public function __construct(
        private ActivityFramerReadMapper $activityFramerReadMapper,
    ) {
    }

    /**
     * @param list<array{member: Member, availability?: AvailabilityEnum|null}> $framers
     */
    public function mapToView(array $framers, string $referer): ListDrawerView
    {
        return new ListDrawerView(
            title: 'Encadrant',
            description: 'Liste des disponibiltés par encaddrants',
            items: array_map(fn (array $framer) => $this->activityFramerReadMapper->mapToView(
                $framer['member'],
                $framer['availability'] ?? AvailabilityEnum::NONE,
                $referer
            ), $framers),
            empty: new EmptyView(icon: 'lucide:shield-user', message: 'Aucun encadrant')
        );
    }
}
