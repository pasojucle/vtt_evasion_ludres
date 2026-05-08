<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Dto\Enum\ActivityPeriod;
use App\Dto\Filter\ActivityFilter;
use App\Dto\ListDto;
use App\Entity\BikeRide;
use App\Mapper\Activity\ActivityAdminListMapper;
use App\Repository\BikeRideRepository;
use App\Repository\SessionRepository;
use App\Service\PaginatorService;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ActivityAdminListProvider
{
    public function __construct(
        private BikeRideRepository $bikeRideRepository,
        private SessionRepository $sessionRepository,
        private PaginatorService $paginator,
        private ActivityAdminListMapper $mapper,
    ) {
    }
    
    public function getCollection(ActivityFilter $filter, string $route, ?int $currentPage = 1): ListDto
    {
        $qb = $this->bikeRideRepository->findActivityQuery();
        match ($filter->period) {
            ActivityPeriod::UPCOMING => $this->bikeRideRepository->filterUpcoming($qb, new DateTime()),
            ActivityPeriod::MONTH => $this->bikeRideRepository->filterByMonth($qb, ...$this->getInterval($filter->month)),
            default => $this->bikeRideRepository->filterAllDesc($qb),
        };

        $entities = $this->paginator->paginate(
            $qb,
            $currentPage,
            PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $this->getParticipantTotalByActivity($entities),
            $route,
            $currentPage,
            $filter
        );
    }

    private function getInterval(?string $month): array
    {
        if (!$month) {
            $month = (new DateTime())->format('Y-m');
        }

        $firstDay = new DateTimeImmutable("$month-01");
        return [
            $firstDay,
            $firstDay->modify('last day of this month')
        ];
    }

    private function getParticipantTotalByActivity(Paginator $entities): array
    {
        $entityIds = array_map(fn ($e) => $e->getId(), iterator_to_array($entities));
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
