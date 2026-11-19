<?php

declare(strict_types=1);

namespace App\State\Activity\Trait;

trait ActivityListDataTrait
{
    /**
     * @param array<int> $entityIds
     */
    private function getParticipantTotalByActivity(array $entityIds): array
    {
        $results = $this->sessionRepository->findTotalByActivityIds($entityIds);

        return array_reduce($results, function ($acc, $item) {
            $acc[$item['id']] = [
                'count' => (int) $item['count'],
                'present' => (int) $item['present']
            ];
            return $acc;
        }, []);
    }
}
