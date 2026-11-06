<?php

declare(strict_types=1);

namespace App\Mapper\Activity;

use App\Entity\BikeRide;

class ActivityAutocompleteMapper
{
    /**
     * @param BikeRide[] $activities
     */
    public function mapToChoices(array $activities): array
    {
        $results = [];
        foreach ($activities as $activity) {
            $results[] = [
                'value' => $activity->getId(),
                'text' => $activity->__toString(),
            ];
        }

        return $results;
    }
}
