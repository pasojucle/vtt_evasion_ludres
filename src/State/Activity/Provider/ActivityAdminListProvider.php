<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Dto\Enum\ActivityPeriod;
use App\Dto\Enum\ActivityRestriction;
use App\Dto\Enum\ActivityVisibility;
use App\Dto\Filter\ActivityFilter;
use App\Dto\ListDto;
use App\Mapper\Activity\ActivityAdminListMapper;
use App\Mapper\FilterMapper;
use App\Repository\BikeRideRepository;
use App\Repository\SessionRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use function PHPUnit\Framework\matches;

class ActivityAdminListProvider
{
    public function __construct(
        private BikeRideRepository $bikeRideRepository,
        private SessionRepository $sessionRepository,
        private PaginatorService $paginator,
        private ActivityAdminListMapper $mapper,
        private FilterMapper $filterMapper,
        #[TaggedIterator('app.filter_config')]
        private iterable $filterConfigs,
    ) {
    }
    
    public function getCollection(ActivityFilter $filter, string $route, ?int $currentPage = 1): ListDto
    {
        $qb = $this->bikeRideRepository->findActivityQuery();
        match ($filter->period) {
            ActivityPeriod::UPCOMING => $this->bikeRideRepository->filterUpcoming($qb, new DateTime()),
            ActivityPeriod::MONTH => $this->bikeRideRepository->filterByMonth($qb, ...$this->getInterval($filter->month)),
            default => null,
        };
        
        if ($filter->sort) {
            $this->bikeRideRepository->filterSort($qb, $filter->sort);
        }

        if ($filter->type) {
            $this->bikeRideRepository->filterType($qb, $filter->type);
        }

        if ($filter->restriction) {
            match ($filter->restriction) {
                ActivityRestriction::MEMBERS => $this->bikeRideRepository->filterHasMembers($qb),
                ActivityRestriction::AGE => $this->bikeRideRepository->filterHasAge($qb),
            };
        }

        if ($filter->visibility) {
            $this->bikeRideRepository->filterIsPrivate($qb, ActivityVisibility::PRIVATE === $filter->visibility);
        }

        $entities = $this->paginator->paginate(
            $qb,
            $currentPage,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $this->getParticipantTotalByActivity($entities),
            $route,
            $currentPage,
            $filter
        );
    }

    public function getHydratedDto(array $rawData, string $dataClass): ?object
    {
        return $this->filterMapper->mapToDto($rawData, $dataClass);
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

    public function getFilterConfig(string $route): ?FilterConfigInterface
    {
        foreach ($this->filterConfigs as $config) {
            if ($config->supports($route)) {
                return $config;
            }
        }

        return null;
    }
}
