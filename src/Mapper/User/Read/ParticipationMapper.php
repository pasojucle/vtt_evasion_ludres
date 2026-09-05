<?php

declare(strict_types=1);

namespace App\Mapper\User\Read;


use App\Dto\View\User\Tab\ParticipationView;
use App\Entity\User;

class ParticipationMapper
{
    public function mapToView(User $entity, array $seasonPeriod): ParticipationView
    {

        return new ParticipationView(
            id: $entity->getId(),
            participationParams: [
                'member' => $entity->getId(),
                'startAt' => $seasonPeriod['startAt']->format('Y-m-d'),
                'endAt' => $seasonPeriod['endAt']->format('Y-m-d'),
            ],
        );
    }
}
