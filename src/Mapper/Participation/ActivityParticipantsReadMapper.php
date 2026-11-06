<?php

declare(strict_types=1);

namespace App\Mapper\Participation;

use App\Dto\Filter\ActivityParticipantsFilter;
use App\Dto\View\EmptyView;
use App\Dto\View\ListDrawerView;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Session;
use App\Mapper\Participation\ActivityParticipantReadMapper;

class ActivityParticipantsReadMapper
{
    public function __construct(
        private ActivityParticipantReadMapper $activityParticipantReadMapper,
    ) {
    }

    /**
     * @param list<Session> $sessions
     */
    public function mapToView(array $sessions, ActivityParticipantsFilter $filter, string $fallback): ListDrawerView
    {
        return new ListDrawerView(
            title: 'Participants',
            name: 'activity-participants-drawer',
            description: 'Liste des participants',
            items: array_map(fn (Session $session) => $this->activityParticipantReadMapper->mapToView(
                $session,
                $fallback,
            ), $sessions),
            empty: new EmptyView(icon: 'lucide:shield-user', message: 'Aucun encadrant')
        );
    }
}
